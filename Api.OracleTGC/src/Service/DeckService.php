<?php

namespace App\Service;

use App\Core\Domain\Exception\EntityNotFoundException;
use App\Core\Domain\Exception\ValidationException;
use App\Domain\Entity\Card;
use App\Domain\Entity\Deck;
use App\Domain\Entity\DeckCard;
use App\Domain\Entity\Inventory;
use App\Domain\Entity\User;
use App\Repositories\DeckRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Deck Service
 * 
 * Serviço responsável por gerenciamento de decks (apenas premium).
 */
class DeckService
{
    public function __construct(
        private DeckRepository $deckRepository,
        private EntityManagerInterface $em
    ) {
    }

    /**
     * Cria um novo deck
     */
    public function create(Inventory $inventory, string $name, string $game, ?string $format = null, ?string $description = null): Deck
    {
        $deck = new Deck($name, $game, $format);
        $deck->setInventory($inventory);
        
        if ($description) {
            $deck->setDescription($description);
        }

        $this->em->persist($deck);
        $this->em->flush();

        return $deck;
    }

    /**
     * Busca deck por ID
     */
    public function findById(string $id): ?Deck
    {
        return $this->deckRepository->find($id);
    }

    /**
     * Busca decks por inventário
     */
    public function findByInventory(Inventory $inventory): array
    {
        return $this->deckRepository->findByInventoryId($inventory->getId());
    }

    /**
     * Atualiza um deck
     */
    public function update(Deck $deck, array $data): Deck
    {
        if (isset($data['name'])) {
            $deck->setName($data['name']);
        }

        if (isset($data['description'])) {
            $deck->setDescription($data['description']);
        }

        if (isset($data['format'])) {
            $deck->setFormat($data['format']);
        }

        $this->em->flush();

        return $deck;
    }

    /**
     * Adiciona um card ao deck
     */
    public function addCard(Deck $deck, Card $card, int $quantity = 1, string $zone = 'main'): DeckCard
    {
        if ($deck->getGame() !== $card->getGame()) {
            throw ValidationException::fromErrors([
                'game' => 'Card não pertence ao mesmo jogo do deck'
            ]);
        }

        // Verificar se o card já existe no deck
        $existingDeckCard = null;
        foreach ($deck->getCards() as $deckCard) {
            if ($deckCard->getCard()->equals($card) && $deckCard->getZone() === $zone) {
                $existingDeckCard = $deckCard;
                break;
            }
        }

        if ($existingDeckCard) {
            $existingDeckCard->setQuantity($existingDeckCard->getQuantity() + $quantity);
            $this->em->flush();
            return $existingDeckCard;
        }

        // Criar novo DeckCard
        $deckCard = new DeckCard($deck, $card, $quantity, $zone);
        $this->em->persist($deckCard);
        $this->em->flush();

        return $deckCard;
    }

    /**
     * Remove um card do deck
     */
    public function removeCard(Deck $deck, Card $card, string $zone = 'main'): void
    {
        $cards = $deck->getCards();
        foreach ($cards as $deckCard) {
            if ($deckCard->getCard()->equals($card) && $deckCard->getZone() === $zone) {
                $this->em->remove($deckCard);
                $this->em->flush();
                return;
            }
        }

        throw new EntityNotFoundException("Card não encontrado no deck");
    }

    /**
     * Atualiza quantidade de um card no deck
     */
    public function updateCardQuantity(Deck $deck, Card $card, int $quantity, string $zone = 'main'): void
    {
        $cards = $deck->getCards();
        foreach ($cards as $deckCard) {
            if ($deckCard->getCard()->equals($card) && $deckCard->getZone() === $zone) {
                if ($quantity <= 0) {
                    $this->em->remove($deckCard);
                } else {
                    $deckCard->setQuantity($quantity);
                }
                $this->em->flush();
                return;
            }
        }

        throw new EntityNotFoundException("Card não encontrado no deck");
    }

    /**
     * Valida um deck (verifica regras do jogo)
     */
    public function validateDeck(Deck $deck): array
    {
        // Usa o método validate() da entidade Deck
        $deck->validate();
        $this->em->flush();

        return $deck->getValidationErrors() ?? [];
    }

    /**
     * Deleta um deck
     */
    public function delete(Deck $deck): void
    {
        $this->em->remove($deck);
        $this->em->flush();
    }

    /**
     * Verifica se o deck pertence ao inventário do usuário
     */
    public function belongsToUser(Deck $deck, User $user): bool
    {
        return $deck->getInventory()->getUserId() === $user->getId();
    }
}

