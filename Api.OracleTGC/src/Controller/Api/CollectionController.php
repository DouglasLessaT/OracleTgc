<?php

namespace App\Controller\Api;

use App\Core\Domain\Exception\DomainException;
use App\Core\Domain\Exception\EntityNotFoundException;
use App\Core\Presentation\Controller\BaseApiController;
use App\Domain\Entity\Card;
use App\Domain\Entity\User;
use App\Repositories\CardRepository;
use App\Service\AuthService;
use App\Service\CollectionService;
use App\Service\InventoryService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/collections', name: 'api_collections_')]
class CollectionController extends BaseApiController
{
    public function __construct(
        private CollectionService $collectionService,
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

            // Free e Premium podem listar suas coleções; só criar coleções extras é restrito a Premium
            $inventory = $this->inventoryService->getOrCreateInventory($user);
            $collections = $this->collectionService->findByInventory($inventory);
            $collectionsData = array_map(fn($c) => $c->toArray(), $collections);

            return $this->success($collectionsData);
        } catch (\Exception $e) {
            return $this->error('Erro ao listar coleções: ' . $e->getMessage());
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

            if (!$user->canCreateCollections()) {
                return $this->forbidden('Apenas usuários Premium podem criar coleções');
            }

            $data = $this->getRequestData($request);
            $this->validateRequired($data, ['name', 'game']);

            $inventory = $this->inventoryService->getOrCreateInventory($user);
            $collection = $this->collectionService->create(
                $inventory,
                $data['name'],
                $data['game'],
                $data['setCode'] ?? null,
                $data['setName'] ?? null
            );

            return $this->created($collection->toArray(), 'Coleção criada com sucesso');
        } catch (DomainException $e) {
            return $this->handleDomainException($e);
        } catch (\Exception $e) {
            return $this->error('Erro ao criar coleção: ' . $e->getMessage());
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

            $collection = $this->collectionService->findById($id);
            
            if (!$collection) {
                return $this->notFound('Coleção não encontrada');
            }

            if (!$this->collectionService->belongsToUser($collection, $user)) {
                return $this->forbidden('Você não tem permissão para ver esta coleção');
            }

            return $this->success($collection->toArray());
        } catch (\Exception $e) {
            return $this->error('Erro ao buscar coleção: ' . $e->getMessage());
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

            $collection = $this->collectionService->findById($id);
            
            if (!$collection) {
                return $this->notFound('Coleção não encontrada');
            }

            if (!$this->collectionService->belongsToUser($collection, $user)) {
                return $this->forbidden('Você não tem permissão para atualizar esta coleção');
            }

            $data = $this->getRequestData($request);
            $updatedCollection = $this->collectionService->update($collection, $data);

            return $this->success($updatedCollection->toArray(), 'Coleção atualizada com sucesso');
        } catch (DomainException $e) {
            return $this->handleDomainException($e);
        } catch (\Exception $e) {
            return $this->error('Erro ao atualizar coleção: ' . $e->getMessage());
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

            $collection = $this->collectionService->findById($id);
            
            if (!$collection) {
                return $this->notFound('Coleção não encontrada');
            }

            if (!$this->collectionService->belongsToUser($collection, $user)) {
                return $this->forbidden('Você não tem permissão para deletar esta coleção');
            }

            $this->collectionService->delete($collection);

            return $this->noContent();
        } catch (\Exception $e) {
            return $this->error('Erro ao deletar coleção: ' . $e->getMessage());
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

            $collection = $this->collectionService->findById($id);
            
            if (!$collection) {
                return $this->notFound('Coleção não encontrada');
            }

            if (!$this->collectionService->belongsToUser($collection, $user)) {
                return $this->forbidden('Você não tem permissão para modificar esta coleção');
            }

            $data = $this->getRequestData($request);
            $this->validateRequired($data, ['cardId']);

            $card = $this->cardRepository->find($data['cardId']);
            
            if (!$card) {
                return $this->notFound('Card não encontrado');
            }

            $this->collectionService->addCard($collection, $card);

            return $this->success($collection->toArray(), 'Card adicionado à coleção');
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

            $collection = $this->collectionService->findById($id);
            
            if (!$collection) {
                return $this->notFound('Coleção não encontrada');
            }

            if (!$this->collectionService->belongsToUser($collection, $user)) {
                return $this->forbidden('Você não tem permissão para modificar esta coleção');
            }

            $card = $this->cardRepository->find($cardId);
            
            if (!$card) {
                return $this->notFound('Card não encontrado');
            }

            $this->collectionService->removeCard($collection, $card);

            return $this->noContent();
        } catch (\Exception $e) {
            return $this->error('Erro ao remover card: ' . $e->getMessage());
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

