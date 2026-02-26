<?php

namespace App\Controller\Api;

use App\Domain\Entity\Card;
use App\Repositories\CardRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Cartas: busca prioritária no banco local (storage + DB após app:cards:sync-images).
 * Permite identificar preços ao scanear sem depender de APIs externas.
 */
#[Route('/cards', name: 'api_cards_')]
class CardController extends AbstractController
{
    public function __construct(
        private CardRepository $cardRepository,
    ) {
    }

    #[Route('/search', name: 'search', methods: ['GET'])]
    public function search(Request $request): JsonResponse
    {
        $query = $request->query->get('q', '');
        $game = $request->query->get('game'); // 'mtg', 'pokemon', 'onepiece' ou null
        $limit = min(50, max(1, (int) $request->query->get('limit', 20)));

        if (empty($query)) {
            return $this->json(['error' => 'Query parameter "q" is required'], 400);
        }

        $cards = $this->cardRepository->searchByName($query, $game ?: null, $limit);
        $result = array_map(fn(Card $c) => $c->toArray(), $cards);

        return $this->json($result);
    }

    #[Route('/mtg/{setCode}/{number}', name: 'mtg_by_set_number', methods: ['GET'])]
    public function getMTGCardBySetAndNumber(string $setCode, string $number): JsonResponse
    {
        $card = $this->cardRepository->findBySetAndNumber('mtg', $setCode, $number);

        if (!$card) {
            return $this->json(['error' => 'Card not found'], 404);
        }

        return $this->json($card->toArray());
    }

    #[Route('/pokemon/{setCode}/{number}', name: 'pokemon_by_set_number', methods: ['GET'])]
    public function getPokemonCardBySetAndNumber(string $setCode, string $number): JsonResponse
    {
        $card = $this->cardRepository->findBySetAndNumber('pokemon', $setCode, $number);

        if (!$card) {
            return $this->json(['error' => 'Card not found'], 404);
        }

        return $this->json($card->toArray());
    }

    /**
     * Resolve card por set + número (qualquer jogo). Útil após OCR retornar set e número.
     */
    #[Route('/by-set/{game}/{setCode}/{number}', name: 'by_set_number', methods: ['GET'])]
    public function getBySetAndNumber(string $game, string $setCode, string $number): JsonResponse
    {
        $card = $this->cardRepository->findBySetAndNumber($game, $setCode, $number);

        if (!$card) {
            return $this->json(['error' => 'Card not found'], 404);
        }

        return $this->json($card->toArray());
    }
}

