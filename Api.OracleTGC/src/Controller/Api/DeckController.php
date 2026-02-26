<?php

namespace App\Controller\Api;

use App\Core\Domain\Exception\DomainException;
use App\Core\Domain\Exception\EntityNotFoundException;
use App\Core\Presentation\Controller\BaseApiController;
use App\Domain\Entity\Card;
use App\Domain\Entity\User;
use App\Repositories\CardRepository;
use App\Service\AuthService;
use App\Service\DeckService;
use App\Service\InventoryService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/decks', name: 'api_decks_')]
class DeckController extends BaseApiController
{
    public function __construct(
        private DeckService $deckService,
        private InventoryService $inventoryService,
        private AuthService $authService,
        private CardRepository $cardRepository
    ) {
    }

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request): Response
    {
        try {
            $user = $this->getCurrentUser($request);
            
            if (!$user) {
                return $this->unauthorized();
            }

            if (!$user->canCreateDecks()) {
                return $this->forbidden('Apenas usuários Premium podem criar decks');
            }

            $inventory = $this->inventoryService->getOrCreateInventory($user);
            $decks = $this->deckService->findByInventory($inventory);
            $decksData = array_map(fn($d) => $d->toArray(), $decks);

            return $this->success($decksData);
        } catch (\Exception $e) {
            return $this->error('Erro ao listar decks: ' . $e->getMessage());
        }
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): Response
    {
        try {
            $user = $this->getCurrentUser($request);
            
            if (!$user) {
                return $this->unauthorized();
            }

            if (!$user->canCreateDecks()) {
                return $this->forbidden('Apenas usuários Premium podem criar decks');
            }

            $data = $this->getRequestData($request);
            $this->validateRequired($data, ['name', 'game']);

            $inventory = $this->inventoryService->getOrCreateInventory($user);
            $deck = $this->deckService->create(
                $inventory,
                $data['name'],
                $data['game'],
                $data['format'] ?? null,
                $data['description'] ?? null
            );

            return $this->created($deck->toArray(), 'Deck criado com sucesso');
        } catch (DomainException $e) {
            return $this->handleDomainException($e);
        } catch (\Exception $e) {
            return $this->error('Erro ao criar deck: ' . $e->getMessage());
        }
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(string $id, Request $request): Response
    {
        try {
            $user = $this->getCurrentUser($request);
            
            if (!$user) {
                return $this->unauthorized();
            }

            $deck = $this->deckService->findById($id);
            
            if (!$deck) {
                return $this->notFound('Deck não encontrado');
            }

            if (!$this->deckService->belongsToUser($deck, $user)) {
                return $this->forbidden('Você não tem permissão para ver este deck');
            }

            return $this->success($deck->toArray());
        } catch (\Exception $e) {
            return $this->error('Erro ao buscar deck: ' . $e->getMessage());
        }
    }

    #[Route('/{id}', name: 'update', methods: ['PUT', 'PATCH'])]
    public function update(string $id, Request $request): Response
    {
        try {
            $user = $this->getCurrentUser($request);
            
            if (!$user) {
                return $this->unauthorized();
            }

            $deck = $this->deckService->findById($id);
            
            if (!$deck) {
                return $this->notFound('Deck não encontrado');
            }

            if (!$this->deckService->belongsToUser($deck, $user)) {
                return $this->forbidden('Você não tem permissão para atualizar este deck');
            }

            $data = $this->getRequestData($request);
            $updatedDeck = $this->deckService->update($deck, $data);

            return $this->success($updatedDeck->toArray(), 'Deck atualizado com sucesso');
        } catch (DomainException $e) {
            return $this->handleDomainException($e);
        } catch (\Exception $e) {
            return $this->error('Erro ao atualizar deck: ' . $e->getMessage());
        }
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(string $id, Request $request): Response
    {
        try {
            $user = $this->getCurrentUser($request);
            
            if (!$user) {
                return $this->unauthorized();
            }

            $deck = $this->deckService->findById($id);
            
            if (!$deck) {
                return $this->notFound('Deck não encontrado');
            }

            if (!$this->deckService->belongsToUser($deck, $user)) {
                return $this->forbidden('Você não tem permissão para deletar este deck');
            }

            $this->deckService->delete($deck);

            return $this->noContent();
        } catch (\Exception $e) {
            return $this->error('Erro ao deletar deck: ' . $e->getMessage());
        }
    }

    #[Route('/{id}/cards', name: 'add_card', methods: ['POST'])]
    public function addCard(string $id, Request $request): Response
    {
        try {
            $user = $this->getCurrentUser($request);
            
            if (!$user) {
                return $this->unauthorized();
            }

            $deck = $this->deckService->findById($id);
            
            if (!$deck) {
                return $this->notFound('Deck não encontrado');
            }

            if (!$this->deckService->belongsToUser($deck, $user)) {
                return $this->forbidden('Você não tem permissão para modificar este deck');
            }

            $data = $this->getRequestData($request);
            $this->validateRequired($data, ['cardId']);

            $card = $this->cardRepository->find($data['cardId']);
            
            if (!$card) {
                return $this->notFound('Card não encontrado');
            }

            $deckCard = $this->deckService->addCard(
                $deck,
                $card,
                $data['quantity'] ?? 1,
                $data['zone'] ?? 'main'
            );

            // Valida o deck após adicionar card
            $this->deckService->validateDeck($deck);

            return $this->success($deck->toArray(), 'Card adicionado ao deck');
        } catch (DomainException $e) {
            return $this->handleDomainException($e);
        } catch (\Exception $e) {
            return $this->error('Erro ao adicionar card: ' . $e->getMessage());
        }
    }

    #[Route('/{id}/cards/{cardId}', name: 'remove_card', methods: ['DELETE'])]
    public function removeCard(string $id, string $cardId, Request $request): Response
    {
        try {
            $user = $this->getCurrentUser($request);
            
            if (!$user) {
                return $this->unauthorized();
            }

            $deck = $this->deckService->findById($id);
            
            if (!$deck) {
                return $this->notFound('Deck não encontrado');
            }

            if (!$this->deckService->belongsToUser($deck, $user)) {
                return $this->forbidden('Você não tem permissão para modificar este deck');
            }

            $card = $this->cardRepository->find($cardId);
            
            if (!$card) {
                return $this->notFound('Card não encontrado');
            }

            $this->deckService->removeCard($deck, $card, $request->query->get('zone', 'main'));

            // Valida o deck após remover card
            $this->deckService->validateDeck($deck);

            return $this->success($deck->toArray(), 'Card removido do deck');
        } catch (EntityNotFoundException $e) {
            return $this->notFound($e->getMessage());
        } catch (\Exception $e) {
            return $this->error('Erro ao remover card: ' . $e->getMessage());
        }
    }

    #[Route('/{id}/validate', name: 'validate', methods: ['POST'])]
    public function validate(string $id, Request $request): Response
    {
        try {
            $user = $this->getCurrentUser($request);
            
            if (!$user) {
                return $this->unauthorized();
            }

            $deck = $this->deckService->findById($id);
            
            if (!$deck) {
                return $this->notFound('Deck não encontrado');
            }

            if (!$this->deckService->belongsToUser($deck, $user)) {
                return $this->forbidden('Você não tem permissão para validar este deck');
            }

            $errors = $this->deckService->validateDeck($deck);

            return $this->success([
                'isLegal' => $deck->isLegal(),
                'errors' => $errors,
                'deck' => $deck->toArray()
            ]);
        } catch (\Exception $e) {
            return $this->error('Erro ao validar deck: ' . $e->getMessage());
        }
    }

    /**
     * Obtém o usuário atual a partir do token JWT
     */
    private function getCurrentUser(Request $request): ?User
    {
        $token = $request->headers->get('Authorization');
        
        if (!$token || !str_starts_with($token, 'Bearer ')) {
            return null;
        }

        $token = substr($token, 7);
        return $this->authService->validateToken($token);
    }
}

