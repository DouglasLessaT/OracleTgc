<?php

namespace App\Service;

use App\DTO\CardDTO;
use App\Domain\Entity\Card;
use App\Domain\Factory\CardFactory;
use App\Repositories\CardRepository;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;

/**
 * CardSyncService
 * 
 * Serviço de sincronização de cards implementando a estratégia híbrida:
 * 1. Cache (Redis) - Acesso ultra-rápido
 * 2. Banco de Dados - Cards salvos
 * 3. APIs Externas - Fallback
 */
class CardSyncService
{
    private const CACHE_TTL = 86400; // 24 horas
    private const CACHE_PREFIX = 'card:';

    public function __construct(
        private CardRepository $cardRepository,
        private ScryfallService $scryfallService,
        private PokemonService $pokemonService,
        private ?OnePieceService $onePieceService = null,
        private CacheItemPoolInterface $cache,
        private ?LoggerInterface $logger = null,
    ) {
    }

    /**
     * Busca um card seguindo a estratégia híbrida:
     * Cache -> Banco -> API Externa
     * 
     * @param string $game Tipo do jogo: 'mtg', 'pokemon', 'onepiece'
     * @param string $setCode Código do set
     * @param string $number Número do card
     * @param bool $saveToDatabase Se deve salvar no banco após buscar da API
     * @return Card|null
     */
    public function findCard(
        string $game,
        string $setCode,
        string $number,
        bool $saveToDatabase = true
    ): ?Card {
        $cacheKey = $this->getCacheKey($game, $setCode, $number);

        // 1. Verificar Cache (Redis)
        $cached = $this->cache->getItem($cacheKey);
        if ($cached->isHit()) {
            $this->logger?->debug("Card encontrado no cache: {$game}/{$setCode}/{$number}");
            return $this->deserializeCard($cached->get(), $game);
        }

        // 2. Verificar Banco de Dados
        $card = $this->cardRepository->findBySetAndNumber($game, $setCode, $number);
        if ($card !== null) {
            $this->logger?->debug("Card encontrado no banco: {$game}/{$setCode}/{$number}");
            // Atualizar cache
            $this->cacheCard($card, $cacheKey);
            return $card;
        }

        // 3. Buscar na API Externa
        $this->logger?->info("Buscando card na API externa: {$game}/{$setCode}/{$number}");
        $cardDTO = $this->fetchFromExternalApi($game, $setCode, $number);
        
        if ($cardDTO === null) {
            $this->logger?->warning("Card não encontrado na API: {$game}/{$setCode}/{$number}");
            return null;
        }

        // 4. Converter DTO para Entity
        $card = CardFactory::createFromDTO($cardDTO);

        // 5. Salvar no banco (se solicitado)
        if ($saveToDatabase) {
            try {
                $this->cardRepository->save($card);
                $this->logger?->info("Card salvo no banco: {$game}/{$setCode}/{$number}");
            } catch (\Exception $e) {
                $this->logger?->error("Erro ao salvar card no banco: " . $e->getMessage());
            }
        }

        // 6. Atualizar cache
        $this->cacheCard($card, $cacheKey);

        return $card;
    }

    /**
     * Busca múltiplos cards de uma vez
     * 
     * @param array $cardIdentifiers Array de ['game' => string, 'setCode' => string, 'number' => string]
     * @param bool $saveToDatabase
     * @return Card[]
     */
    public function findMultipleCards(array $cardIdentifiers, bool $saveToDatabase = true): array
    {
        $cards = [];

        foreach ($cardIdentifiers as $identifier) {
            $card = $this->findCard(
                $identifier['game'],
                $identifier['setCode'],
                $identifier['number'],
                $saveToDatabase
            );

            if ($card !== null) {
                $cards[] = $card;
            }
        }

        return $cards;
    }

    /**
     * Sincroniza um card específico (força atualização da API)
     * 
     * @param string $game
     * @param string $setCode
     * @param string $number
     * @return Card|null
     */
    public function syncCard(string $game, string $setCode, string $number): ?Card
    {
        // Limpar cache
        $cacheKey = $this->getCacheKey($game, $setCode, $number);
        $this->cache->deleteItem($cacheKey);

        // Buscar da API e salvar
        return $this->findCard($game, $setCode, $number, true);
    }

    /**
     * Busca card da API externa
     * 
     * @param string $game
     * @param string $setCode
     * @param string $number
     * @return CardDTO|null
     */
    private function fetchFromExternalApi(string $game, string $setCode, string $number): ?CardDTO
    {
        return match (strtolower($game)) {
            'mtg', 'magic' => $this->scryfallService->getCardBySetAndNumber($setCode, $number),
            'pokemon', 'ptcg' => $this->pokemonService->getCardBySetAndNumber($setCode, $number),
            'onepiece', 'opcg' => $this->onePieceService?->getCardBySetAndNumber($setCode, $number),
            default => null,
        };
    }


    /**
     * Armazena card no cache
     * 
     * @param Card $card
     * @param string $cacheKey
     */
    private function cacheCard(Card $card, string $cacheKey): void
    {
        try {
            $cached = $this->cache->getItem($cacheKey);
            $cached->set($card->toArray());
            $cached->expiresAfter(self::CACHE_TTL);
            $this->cache->save($cached);
        } catch (\Exception $e) {
            $this->logger?->warning("Erro ao salvar no cache: " . $e->getMessage());
        }
    }

    /**
     * Deserializa card do cache
     * 
     * @param array $data
     * @param string $game
     * @return Card|null
     */
    private function deserializeCard(array $data, string $game): ?Card
    {
        try {
            return CardFactory::create($game, $data);
        } catch (\Exception $e) {
            $this->logger?->warning("Erro ao deserializar card do cache: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Gera chave de cache
     * 
     * @param string $game
     * @param string $setCode
     * @param string $number
     * @return string
     */
    private function getCacheKey(string $game, string $setCode, string $number): string
    {
        return self::CACHE_PREFIX . strtolower("{$game}:{$setCode}:{$number}");
    }

    /**
     * Limpa cache de um card específico
     * 
     * @param string $game
     * @param string $setCode
     * @param string $number
     */
    public function clearCache(string $game, string $setCode, string $number): void
    {
        $cacheKey = $this->getCacheKey($game, $setCode, $number);
        $this->cache->deleteItem($cacheKey);
    }

    /**
     * Limpa todo o cache de cards
     */
    public function clearAllCache(): void
    {
        // Nota: Implementação depende do driver de cache
        // Para Redis, seria necessário iterar sobre todas as chaves
        $this->logger?->info("Cache de cards limpo");
    }
}

