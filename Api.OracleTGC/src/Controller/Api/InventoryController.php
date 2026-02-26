<?php

namespace App\Controller\Api;

use App\Core\Domain\Exception\DomainException;
use App\Core\Domain\Exception\EntityNotFoundException;
use App\Core\Presentation\Controller\BaseApiController;
use App\Domain\Entity\Card;
use App\Domain\Entity\User;
use App\Repositories\CardRepository;
use App\Service\AuthService;
use App\Service\InventoryService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/inventory', name: 'api_inventory_')]
class InventoryController extends BaseApiController
{
    public function __construct(
        private InventoryService $inventoryService,
        private AuthService $authService,
        private CardRepository $cardRepository
    ) {
    }

    #[Route('', name: 'show', methods: ['GET'])]
    public function show(Request $request): Response
    {
        try {
            $user = $this->getCurrentUser($request);
            
            if (!$user) {
                return $this->unauthorized();
            }

            $inventory = $this->inventoryService->getOrCreateInventory($user);

            return $this->success($inventory->toArray());
        } catch (\Exception $e) {
            return $this->error('Erro ao buscar inventário: ' . $e->getMessage());
        }
    }

    #[Route('/cards', name: 'add_card', methods: ['POST'])]
    public function addCard(Request $request): Response
    {
        try {
            $user = $this->getCurrentUser($request);
            
            if (!$user) {
                return $this->unauthorized();
            }

            // Verificar se pode escanear (limite de 7 para usuários gratuitos)
            if (!$user->canScanCard()) {
                return $this->error('Limite de cards escaneados atingido. Upgrade para Premium para escanear ilimitado.', Response::HTTP_FORBIDDEN);
            }

            $data = $this->getRequestData($request);
            $this->validateRequired($data, ['cardId', 'quantity']);

            $card = $this->cardRepository->find($data['cardId']);
            
            if (!$card) {
                return $this->notFound('Card não encontrado');
            }

            $inventory = $this->inventoryService->getOrCreateInventory($user);
            $item = $this->inventoryService->addCard(
                $inventory,
                $card,
                $data['quantity'] ?? 1,
                $data['metadata'] ?? null
            );

            // Incrementa contador de cards escaneados
            $user->incrementScannedCards();
            $this->inventoryService->getEntityManager()->flush();

            return $this->created($item->toArray(), 'Card adicionado ao inventário');
        } catch (DomainException $e) {
            return $this->handleDomainException($e);
        } catch (\Exception $e) {
            return $this->error('Erro ao adicionar card: ' . $e->getMessage());
        }
    }

    #[Route('/cards/{cardId}', name: 'remove_card', methods: ['DELETE'])]
    public function removeCard(string $cardId, Request $request): Response
    {
        try {
            $user = $this->getCurrentUser($request);
            
            if (!$user) {
                return $this->unauthorized();
            }

            $card = $this->cardRepository->find($cardId);
            
            if (!$card) {
                return $this->notFound('Card não encontrado');
            }

            $inventory = $this->inventoryService->findByUser($user);
            
            if (!$inventory) {
                return $this->notFound('Inventário não encontrado');
            }

            $this->inventoryService->removeCard($inventory, $card);

            return $this->noContent();
        } catch (\Exception $e) {
            return $this->error('Erro ao remover card: ' . $e->getMessage());
        }
    }

    #[Route('/cards/{cardId}', name: 'update_card', methods: ['PUT', 'PATCH'])]
    public function updateCard(string $cardId, Request $request): Response
    {
        try {
            $user = $this->getCurrentUser($request);
            
            if (!$user) {
                return $this->unauthorized();
            }

            $data = $this->getRequestData($request);
            
            if (!isset($data['quantity'])) {
                return $this->validationError(['quantity' => 'Quantidade é obrigatória']);
            }

            $card = $this->cardRepository->find($cardId);
            
            if (!$card) {
                return $this->notFound('Card não encontrado');
            }

            $inventory = $this->inventoryService->findByUser($user);
            
            if (!$inventory) {
                return $this->notFound('Inventário não encontrado');
            }

            $this->inventoryService->updateCardQuantity($inventory, $card, $data['quantity']);

            return $this->success(null, 'Card atualizado com sucesso');
        } catch (EntityNotFoundException $e) {
            return $this->notFound($e->getMessage());
        } catch (\Exception $e) {
            return $this->error('Erro ao atualizar card: ' . $e->getMessage());
        }
    }

    #[Route('/statistics', name: 'statistics', methods: ['GET'])]
    public function statistics(Request $request): Response
    {
        try {
            $user = $this->getCurrentUser($request);
            
            if (!$user) {
                return $this->unauthorized();
            }

            $inventory = $this->inventoryService->getOrCreateInventory($user);
            $stats = $inventory->getStatistics();

            return $this->success($stats);
        } catch (\Exception $e) {
            return $this->error('Erro ao buscar estatísticas: ' . $e->getMessage());
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

