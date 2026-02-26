<?php

namespace App\Service;

use App\DTO\CardDTO;
use App\DTO\CardPricesDTO;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * OnePieceService
 * 
 * Serviço para buscar cards de One Piece Card Game.
 * 
 * Nota: A API pública de One Piece pode variar. Este serviço é um template
 * que pode ser adaptado conforme a API disponível.
 */
class OnePieceService
{
    private const API_BASE = 'https://api.onepiece-cardgame.com'; // Exemplo - ajustar conforme necessário
    private const CACHE_TTL = 300; // 5 minutes

    public function __construct(
        private HttpClientInterface $httpClient,
        private CacheItemPoolInterface $cache,
        private ?string $apiKey = null,
    ) {
        $this->apiKey = $_ENV['ONEPIECE_API_KEY'] ?? null;
    }

    /**
     * Busca cards por nome
     * 
     * @param string $query Nome do card
     * @return CardDTO[]
     */
    public function searchCard(string $query): array
    {
        $cacheKey = 'onepiece_search_' . md5($query);
        $cached = $this->cache->getItem($cacheKey);

        if ($cached->isHit()) {
            return $cached->get();
        }

        try {
            $headers = [];
            if ($this->apiKey) {
                $headers['Authorization'] = 'Bearer ' . $this->apiKey;
            }

            $response = $this->httpClient->request('GET', self::API_BASE . '/cards', [
                'query' => [
                    'q' => $query,
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
     * Busca card por set e número
     * 
     * @param string $setCode Código do set
     * @param string $number Número do card
     * @return CardDTO|null
     */
    public function getCardBySetAndNumber(string $setCode, string $number): ?CardDTO
    {
        $cacheKey = 'onepiece_card_' . md5($setCode . $number);
        $cached = $this->cache->getItem($cacheKey);

        if ($cached->isHit()) {
            return $cached->get();
        }

        try {
            $headers = [];
            if ($this->apiKey) {
                $headers['Authorization'] = 'Bearer ' . $this->apiKey;
            }

            // Ajustar endpoint conforme a API real
            $response = $this->httpClient->request('GET', self::API_BASE . '/cards', [
                'query' => [
                    'set' => $setCode,
                    'number' => $number,
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

    /**
     * Mapeia dados da API para CardDTO
     * 
     * @param array $cardData
     * @return CardDTO
     */
    private function mapToCardDTO(array $cardData): CardDTO
    {
        // Ajustar mapeamento conforme a estrutura real da API
        $prices = new CardPricesDTO(
            usd: isset($cardData['prices']['usd']) ? (float) $cardData['prices']['usd'] : null,
            usdFoil: isset($cardData['prices']['usd_foil']) ? (float) $cardData['prices']['usd_foil'] : null,
        );

        return new CardDTO(
            id: $cardData['id'] ?? '',
            name: $cardData['name'] ?? '',
            game: 'onepiece',
            set: strtoupper($cardData['set']['code'] ?? $cardData['set'] ?? ''),
            setName: $cardData['set']['name'] ?? '',
            number: $cardData['number'] ?? '',
            rarity: $cardData['rarity'] ?? '',
            imageUrl: $cardData['image'] ?? $cardData['images']['large'] ?? '',
            artist: $cardData['artist'] ?? null,
            isFoil: $cardData['foil'] ?? false,
            prices: $prices,
            type: $cardData['category'] ?? null,
            text: $cardData['effect'] ?? null,
        );
    }
}



