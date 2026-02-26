<?php

namespace App\Service;

use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * SetSyncService
 * 
 * Serviço para sincronizar metadados de sets (expansões) dos jogos.
 * Armazena apenas metadados leves (código, nome, data de lançamento, etc).
 */
class SetSyncService
{
    private const CACHE_TTL = 86400; // 24 horas
    private const CACHE_PREFIX = 'sets:';

    public function __construct(
        private ScryfallService $scryfallService,
        private PokemonService $pokemonService,
        private ?OnePieceService $onePieceService = null,
        private CacheItemPoolInterface $cache,
        private ?LoggerInterface $logger = null,
    ) {
    }

    /**
     * Sincroniza todos os sets de um jogo
     * 
     * @param string $game Tipo do jogo: 'mtg', 'pokemon', 'onepiece'
     * @return array Array de sets com metadados
     */
    public function syncSets(string $game): array
    {
        $cacheKey = $this->getCacheKey($game);
        $cached = $this->cache->getItem($cacheKey);

        if ($cached->isHit()) {
            $this->logger?->debug("Sets encontrados no cache para: {$game}");
            return $cached->get();
        }

        $this->logger?->info("Sincronizando sets de: {$game}");

        $sets = match (strtolower($game)) {
            'mtg', 'magic' => $this->syncMTGSets(),
            'pokemon', 'ptcg' => $this->syncPokemonSets(),
            'onepiece', 'opcg' => $this->syncOnePieceSets(),
            default => [],
        };

        // Salvar no cache
        $cached->set($sets);
        $cached->expiresAfter(self::CACHE_TTL);
        $this->cache->save($cached);

        return $sets;
    }

    /**
     * Sincroniza sets de Magic: The Gathering
     * 
     * @return array
     */
    private function syncMTGSets(): array
    {
        try {
            // Scryfall API para buscar sets
            // Nota: ScryfallService precisa ter um método getAllSets()
            // Por enquanto, retornamos array vazio como placeholder
            $this->logger?->info("Buscando sets de MTG via Scryfall");
            
            // TODO: Implementar busca de sets via Scryfall API
            // Exemplo: GET https://api.scryfall.com/sets
            
            return [];
        } catch (\Exception $e) {
            $this->logger?->error("Erro ao sincronizar sets de MTG: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Sincroniza sets de Pokémon TCG
     * 
     * @return array
     */
    private function syncPokemonSets(): array
    {
        try {
            $this->logger?->info("Buscando sets de Pokémon via API");
            
            // TODO: Implementar busca de sets via Pokémon TCG API
            // Exemplo: GET https://api.pokemontcg.io/v2/sets
            
            return [];
        } catch (\Exception $e) {
            $this->logger?->error("Erro ao sincronizar sets de Pokémon: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Sincroniza sets de One Piece Card Game
     * 
     * @return array
     */
    private function syncOnePieceSets(): array
    {
        try {
            $this->logger?->info("Buscando sets de One Piece via API");
            
            // TODO: Implementar busca de sets via One Piece API
            
            return [];
        } catch (\Exception $e) {
            $this->logger?->error("Erro ao sincronizar sets de One Piece: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Busca um set específico
     * 
     * @param string $game
     * @param string $setCode
     * @return array|null
     */
    public function getSet(string $game, string $setCode): ?array
    {
        $sets = $this->syncSets($game);
        
        foreach ($sets as $set) {
            if (strtoupper($set['code'] ?? '') === strtoupper($setCode)) {
                return $set;
            }
        }

        return null;
    }

    /**
     * Limpa cache de sets
     * 
     * @param string|null $game Se null, limpa todos os jogos
     */
    public function clearCache(?string $game = null): void
    {
        if ($game !== null) {
            $cacheKey = $this->getCacheKey($game);
            $this->cache->deleteItem($cacheKey);
        } else {
            // Limpar todos os jogos
            foreach (['mtg', 'pokemon', 'onepiece'] as $g) {
                $cacheKey = $this->getCacheKey($g);
                $this->cache->deleteItem($cacheKey);
            }
        }
    }

    /**
     * Gera chave de cache
     * 
     * @param string $game
     * @return string
     */
    private function getCacheKey(string $game): string
    {
        return self::CACHE_PREFIX . strtolower($game);
    }
}



