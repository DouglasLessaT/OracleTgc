<?php

namespace App\Service;

use App\DTO\CardDTO;
use App\DTO\CardPricesDTO;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ScryfallService
{
    private const API_BASE = 'https://api.scryfall.com';
    private const CACHE_TTL = 300; // 5 minutes

    public function __construct(
        private HttpClientInterface $httpClient,
        private CacheItemPoolInterface $cache,
    ) {
    }

    public function searchCard(string $query): array
    {
        $cacheKey = 'scryfall_search_' . md5($query);
        $cached = $this->cache->getItem($cacheKey);

        if ($cached->isHit()) {
            return $cached->get();
        }

        try {
            $response = $this->httpClient->request('GET', self::API_BASE . '/cards/search', [
                'query' => [
                    'q' => $query,
                    'order' => 'released',
                    'dir' => 'desc',
                ],
                'timeout' => 5,
            ]);

            if ($response->getStatusCode() === 404) {
                return [];
            }

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
     * Lista todos os sets (código e nome) para sincronização.
     * @return array<int, array{code: string, name: string}>
     */
    public function fetchAllSets(): array
    {
        try {
            $response = $this->httpClient->request('GET', self::API_BASE . '/sets', ['timeout' => 10]);
            $data = $response->toArray();
            $sets = [];
            foreach ($data['data'] ?? [] as $set) {
                $code = $set['code'] ?? '';
                if ($code !== '' && ($set['set_type'] ?? '') !== 'token') {
                    $sets[] = ['code' => strtoupper($code), 'name' => $set['name'] ?? $code];
                }
            }
            return $sets;
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Itera sobre todas as cartas de um set (paginação automática).
     * @return \Generator<array>
     */
    public function fetchCardsInSet(string $setCode): \Generator
    {
        $url = self::API_BASE . '/cards/search?q=set:' . strtolower($setCode) . '&order=set&dir=asc';
        do {
            try {
                $response = $this->httpClient->request('GET', $url, ['timeout' => 15]);
                $data = $response->toArray();
            } catch (\Throwable $e) {
                break;
            }
            foreach ($data['data'] ?? [] as $cardData) {
                yield $cardData;
            }
            $url = $data['next_page'] ?? null;
        } while ($url);
    }

    public function getCardBySetAndNumber(string $setCode, string $number): ?CardDTO
    {
        $cacheKey = 'scryfall_card_' . md5($setCode . $number);
        $cached = $this->cache->getItem($cacheKey);

        if ($cached->isHit()) {
            return $cached->get();
        }

        try {
            $response = $this->httpClient->request('GET', sprintf(
                '%s/cards/%s/%s',
                self::API_BASE,
                strtolower($setCode),
                $number
            ), [
                'timeout' => 5,
            ]);

            if ($response->getStatusCode() === 404) {
                return null;
            }

            $cardData = $response->toArray();
            $card = $this->mapToCardDTO($cardData);

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
        $prices = new CardPricesDTO(
            usd: isset($cardData['prices']['usd']) ? (float) $cardData['prices']['usd'] : null,
            usdFoil: isset($cardData['prices']['usd_foil']) ? (float) $cardData['prices']['usd_foil'] : null,
        );

        return new CardDTO(
            id: $cardData['id'],
            name: $cardData['name'],
            game: 'mtg',
            set: strtoupper($cardData['set'] ?? ''),
            setName: $cardData['set_name'] ?? '',
            number: $cardData['collector_number'] ?? '',
            rarity: $cardData['rarity'] ?? '',
            imageUrl: $cardData['image_uris']['normal'] ?? $cardData['card_faces'][0]['image_uris']['normal'] ?? '',
            artist: $cardData['artist'] ?? null,
            isFoil: $cardData['foil'] ?? false,
            prices: $prices,
            type: $cardData['type_line'] ?? null,
            text: $cardData['oracle_text'] ?? null,
        );
    }
}

