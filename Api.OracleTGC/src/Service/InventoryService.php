<?php

namespace App\Service;

use App\Core\Domain\Exception\EntityNotFoundException;
use App\Domain\Entity\Card;
use App\Domain\Entity\Inventory;
use App\Domain\Entity\InventoryItem;
use App\Domain\Entity\User;
use App\Repositories\InventoryRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Inventory Service
 * 
 * Serviço responsável por gerenciamento de inventários.
 */
class InventoryService
{
    public function __construct(
        private InventoryRepository $inventoryRepository,
        private EntityManagerInterface $em
    ) {
    }

    /**
     * Cria ou retorna o inventário de um usuário
     */
    public function getOrCreateInventory(User $user): Inventory
    {
        $inventory = $this->inventoryRepository->findByUserId($user->getId());

        if (!$inventory) {
            $inventory = new Inventory($user->getId(), 'Meu Inventário');
            $this->em->persist($inventory);
            $this->em->flush();
        }

        return $inventory;
    }

    /**
     * Busca inventário por ID
     */
    public function findById(string $id): ?Inventory
    {
        return $this->inventoryRepository->find($id);
    }

    /**
     * Busca inventário por usuário
     */
    public function findByUser(User $user): ?Inventory
    {
        return $this->inventoryRepository->findByUserId($user->getId());
    }

    /**
     * Adiciona um card ao inventário
     */
    public function addCard(Inventory $inventory, Card $card, int $quantity = 1, ?array $metadata = null): InventoryItem
    {
        // Verificar se o card já existe no inventário
        $existingItem = null;
        foreach ($inventory->getItems() as $item) {
            if ($item->getCard()->equals($card)) {
                $existingItem = $item;
                break;
            }
        }

        if ($existingItem) {
            // Incrementa quantidade
            $existingItem->incrementQuantity($quantity);
            if ($metadata) {
                $existingItem->setMetadata($metadata);
            }
            $this->em->flush();
            return $existingItem;
        }

        // Cria novo item
        $item = $inventory->addCard($card, $quantity, $metadata);
        $this->em->persist($item);
        $this->em->flush();

        return $item;
    }

    /**
     * Remove um card do inventário
     */
    public function removeCard(Inventory $inventory, Card $card): void
    {
        $inventory->removeCard($card);
        $this->em->flush();
    }

    /**
     * Atualiza quantidade de um card no inventário
     */
    public function updateCardQuantity(Inventory $inventory, Card $card, int $quantity): void
    {
        foreach ($inventory->getItems() as $item) {
            if ($item->getCard()->equals($card)) {
                $item->setQuantity($quantity);
                $this->em->flush();
                return;
            }
        }

        throw new EntityNotFoundException("Card não encontrado no inventário");
    }

    /**
     * Verifica se o inventário pertence ao usuário
     */
    public function belongsToUser(Inventory $inventory, User $user): bool
    {
        return $inventory->getUserId() === $user->getId();
    }

    /**
     * Retorna o EntityManager (para uso em controllers quando necessário)
     */
    public function getEntityManager(): EntityManagerInterface
    {
        return $this->em;
    }
}

