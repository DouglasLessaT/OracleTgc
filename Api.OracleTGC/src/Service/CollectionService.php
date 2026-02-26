<?php

namespace App\Service;

use App\Core\Domain\Exception\EntityNotFoundException;
use App\Core\Domain\Exception\ValidationException;
use App\Domain\Entity\Card;
use App\Domain\Entity\Collection;
use App\Domain\Entity\Inventory;
use App\Domain\Entity\User;
use App\Repositories\CollectionRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Collection Service
 * 
 * Serviço responsável por gerenciamento de coleções (apenas premium).
 */
class CollectionService
{
    public function __construct(
        private CollectionRepository $collectionRepository,
        private EntityManagerInterface $em
    ) {
    }

    /**
     * Cria uma nova coleção
     */
    public function create(Inventory $inventory, string $name, string $game, ?string $setCode = null, ?string $setName = null): Collection
    {
        $collection = new Collection($name, $game);
        $collection->setInventory($inventory);
        
        if ($setCode) {
            $collection->setSetCode($setCode);
        }
        
        if ($setName) {
            $collection->setSetName($setName);
        }

        $this->em->persist($collection);
        $this->em->flush();

        return $collection;
    }

    /**
     * Busca coleção por ID
     */
    public function findById(string $id): ?Collection
    {
        return $this->collectionRepository->find($id);
    }

    /**
     * Busca coleções por inventário
     */
    public function findByInventory(Inventory $inventory): array
    {
        return $this->collectionRepository->findByInventoryId($inventory->getId());
    }

    /**
     * Atualiza uma coleção
     */
    public function update(Collection $collection, array $data): Collection
    {
        if (isset($data['name'])) {
            $collection->setName($data['name']);
        }

        if (isset($data['description'])) {
            $collection->setDescription($data['description']);
        }

        if (isset($data['setCode'])) {
            $collection->setSetCode($data['setCode']);
        }

        if (isset($data['setName'])) {
            $collection->setSetName($data['setName']);
        }

        if (isset($data['targetCount'])) {
            $collection->setTargetCount($data['targetCount']);
        }

        $this->em->flush();

        return $collection;
    }

    /**
     * Adiciona um card à coleção
     */
    public function addCard(Collection $collection, Card $card): void
    {
        if ($collection->getGame() !== $card->getGame()) {
            throw ValidationException::fromErrors([
                'game' => 'Card não pertence ao mesmo jogo da coleção'
            ]);
        }

        $collection->addCard($card);
        $this->em->flush();
    }

    /**
     * Remove um card da coleção
     */
    public function removeCard(Collection $collection, Card $card): void
    {
        $collection->removeCard($card);
        $this->em->flush();
    }

    /**
     * Deleta uma coleção
     */
    public function delete(Collection $collection): void
    {
        $this->em->remove($collection);
        $this->em->flush();
    }

    /**
     * Verifica se a coleção pertence ao inventário do usuário
     */
    public function belongsToUser(Collection $collection, User $user): bool
    {
        return $collection->getInventory()->getUserId() === $user->getId();
    }
}

