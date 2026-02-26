<?php

namespace App\Service;

use App\DTO\CardDTO;
use App\DTO\CardPricesDTO;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class PokemonService
{
    private const API_BASE = 'https://api.pokemontcg.io/v2';
    private const CACHE_TTL = 300; // 5 minutes

    public function __construct(
        private HttpClientInterface $httpClient,
        private CacheItemPoolInterface $cache,
        private ?string $apiKey = null,
    ) {
        $this->apiKey = $_ENV['POKEMON_TCG_API_KEY'] ?? null;
    }

    public function searchCard(string $query): array
    {
        $cacheKey = 'pokemon_search_' . md5($query);
        $cached = $this->cache->getItem($cacheKey);

        if ($cached->isHit()) {
            return $cached->get();
        }

        try {
            $headers = [];
            if ($this->apiKey) {
                $headers['X-Api-Key'] = $this->apiKey;
            }

            $response = $this->httpClient->request('GET', self::API_BASE . '/cards', [
                'query' => [
                    'q' => 'name:"' . $query . '"',
                ],
                'headers' => $headers,
                'timeout' => 5,
            ]);

            $data = $response->toArray();
            $cards = [];

            foreach (array_slice($data['data'] ?? [], 0, 5) as $cardData) {
                $cards[] = $this->mapToCardDTO($cardData);
            }

            $cached->set($cards);
            $cached->expiresAfter(self::CACHE_TTL);
            $this->cache->save($cached);

            return $cards;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Lista todos os sets para sincronização.
     * @return array<int, array{code: string, name: string}>
     */
    public function fetchAllSets(): array
    {
        try {
            $headers = $this->apiKey ? ['X-Api-Key' => $this->apiKey] : [];
            $response = $this->httpClient->request('GET', self::API_BASE . '/sets', [
                'headers' => $headers,
                'timeout' => 10,
            ]);
            $data = $response->toArray();
            $sets = [];
            foreach ($data['data'] ?? [] as $set) {
                $id = $set['id'] ?? '';
                if ($id !== '') {
                    $sets[] = ['code' => strtoupper($id), 'name' => $set['name'] ?? $id];
                }
            }
            return $sets;
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Itera sobre todas as cartas de um set (paginação).
     * @return \Generator<array>
     */
    public function fetchCardsInSet(string $setCode): \Generator
    {
        $page = 1;
        $pageSize = 250;
        do {
            try {
                $headers = $this->apiKey ? ['X-Api-Key' => $this->apiKey] : [];
                $response = $this->httpClient->request('GET', self::API_BASE . '/cards', [
                    'query' => [
                        'q' => 'set.id:' . strtolower($setCode),
                        'pageSize' => $pageSize,
                        'page' => $page,
                    ],
                    'headers' => $headers,
                    'timeout' => 15,
                ]);
                $data = $response->toArray();
            } catch (\Throwable $e) {
                break;
            }
            $cards = $data['data'] ?? [];
            foreach ($cards as $cardData) {
                yield $cardData;
            }
            $page++;
        } while (count($cards) === $pageSize);
    }

    public function getCardBySetAndNumber(string $setCode, string $number): ?CardDTO
    {
        $cacheKey = 'pokemon_card_' . md5($setCode . $number);
        $cached = $this->cache->getItem($cacheKey);

        if ($cached->isHit()) {
            return $cached->get();
        }

        try {
            $headers = [];
            if ($this->apiKey) {
                $headers['X-Api-Key'] = $this->apiKey;
            }

            $response = $this->httpClient->request('GET', self::API_BASE . '/cards', [
                'query' => [
                    'q' => sprintf('set.id:%s number:%s', $setCode, $number),
                ],
                'headers' => $headers,
                'timeout' => 5,
            ]);

            $data = $response->toArray();
            if (empty($data['data'])) {
                return null;
            }

            $card = $this->mapToCardDTO($data['data'][0]);

            $cached->set($card);
            $cached->expiresAfter(self::CACHE_TTL);
            $this->cache->save($cached);

            return $card;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function mapResponseToCardDTO(array $cardData): CardDTO
    {
        return $this->mapToCardDTO($cardData);
    }

    private function mapToCardDTO(array $cardData): CardDTO
    {
        $prices = $cardData['tcgplayer']['prices'] ?? [];
        $normalPrice = $prices['normal']['market'] ?? $prices['normal']['mid'] ?? null;
        $holoPrice = $prices['holofoil']['market'] ?? $prices['holofoil']['mid'] ?? 
                     $prices['reverseHolofoil']['market'] ?? $prices['reverseHolofoil']['mid'] ?? null;

        $pricesDTO = new CardPricesDTO(
            usd: $normalPrice ? (float) $normalPrice : null,
            usdFoil: $holoPrice ? (float) $holoPrice : null,
        );

        return new CardDTO(
            id: $cardData['id'],
            name: $cardData['name'],
            game: 'pokemon',
            set: strtoupper($cardData['set']['id'] ?? ''),
            setName: $cardData['set']['name'] ?? '',
            number: $cardData['number'] ?? '',
            rarity: $cardData['rarity'] ?? '',
            imageUrl: $cardData['images']['large'] ?? $cardData['images']['small'] ?? '',
            artist: $cardData['artist'] ?? null,
            isFoil: !empty($holoPrice),
            prices: $pricesDTO,
            type: implode(', ', $cardData['types'] ?? []),
            text: implode("\n", array_map(fn($a) => $a['text'], $cardData['abilities'] ?? [])),
        );
    }
}

