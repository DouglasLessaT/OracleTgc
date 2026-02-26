<?php

namespace App\Domain\Entity;

use App\Core\Domain\Entity\BaseEntity;
use Doctrine\ORM\Mapping as ORM;
use DateTimeImmutable;

/**
 * Inventory
 * 
 * Representa o inventário completo de cards de um usuário.
 * Gerencia todas as cartas escaneadas e suas organizações.
 */
#[ORM\Entity]
#[ORM\Table(name: "inventories")]
class Inventory extends BaseEntity
{
    #[ORM\Id]
    #[ORM\Column(type: "uuid_string", unique: true)]
    protected mixed $id;
    #[ORM\Column(type: "uuid_string")]
    private string $userId;

    #[ORM\Column(length: 255)]
    private string $name;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $description = null;
    
    /** @var \Doctrine\Common\Collections\Collection<int, InventoryItem> */
    #[ORM\OneToMany(mappedBy: "inventory", targetEntity: InventoryItem::class, cascade: ["persist", "remove"], orphanRemoval: true)]
    private \Doctrine\Common\Collections\Collection $items;
    
    /** @var \Doctrine\Common\Collections\Collection<int, Collection> */
    #[ORM\OneToMany(mappedBy: "inventory", targetEntity: Collection::class, cascade: ["persist", "remove"])]
    private \Doctrine\Common\Collections\Collection $collections;
    
    /** @var \Doctrine\Common\Collections\Collection<int, Deck> */
    #[ORM\OneToMany(mappedBy: "inventory", targetEntity: Deck::class, cascade: ["persist", "remove"])]
    private \Doctrine\Common\Collections\Collection $decks;

    public function __construct(string $userId, string $name)
    {
        parent::__construct();
        $this->userId = $userId;
        $this->name = $name;
        $this->items = new \Doctrine\Common\Collections\ArrayCollection();
        $this->collections = new \Doctrine\Common\Collections\ArrayCollection();
        $this->decks = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
        $this->markAsUpdated();
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
        $this->markAsUpdated();
    }

    /**
     * Adiciona um card ao inventário
     */
    public function addCard(Card $card, int $quantity = 1, ?array $metadata = null): InventoryItem
    {
        $item = new InventoryItem($this, $card, $quantity, $metadata);
        $this->items->add($item);
        $this->markAsUpdated();
        return $item;
    }

    /**
     * Remove um card do inventário
     */
    public function removeCard(Card $card): void
    {
        foreach ($this->items as $item) {
            if ($item->getCard()->equals($card)) {
                $this->items->removeElement($item);
                break;
            }
        }
        $this->markAsUpdated();
    }

    /**
     * Retorna todos os items do inventário
     * 
     * @return InventoryItem[]
     */
    public function getItems(): array
    {
        return $this->items->toArray();
    }

    /**
     * Retorna todos os cards (sem duplicatas)
     * 
     * @return Card[]
     */
    public function getAllCards(): array
    {
        return array_map(
            fn(InventoryItem $item) => $item->getCard(),
            $this->items->toArray()
        );
    }

    /**
     * Retorna cards filtrados por jogo
     * 
     * @return Card[]
     */
    public function getCardsByGame(string $game): array
    {
        $items = $this->items->toArray();
        return array_map(
            fn(InventoryItem $item) => $item->getCard(),
            array_filter(
                $items,
                fn(InventoryItem $item) => $item->getCard()->getGame() === $game
            )
        );
    }

    /**
     * Retorna a quantidade total de cards (considerando quantidades)
     */
    public function getTotalCardCount(): int
    {
        return array_reduce(
            $this->items->toArray(),
            fn(int $sum, InventoryItem $item) => $sum + $item->getQuantity(),
            0
        );
    }

    /**
     * Retorna a quantidade de cards únicos
     */
    public function getUniqueCardCount(): int
    {
        return count($this->items);
    }

    /**
     * Adiciona uma coleção
     */
    public function addCollection(Collection $collection): void
    {
        $this->collections->add($collection);
        $this->markAsUpdated();
    }

    /**
     * Remove uma coleção
     */
    public function removeCollection(Collection $collection): void
    {
        $this->collections->removeElement($collection);
        $this->markAsUpdated();
    }

    /**
     * @return Collection[]
     */
    public function getCollections(): array
    {
        return $this->collections->toArray();
    }

    /**
     * Retorna coleções por jogo
     * 
     * @return Collection[]
     */
    public function getCollectionsByGame(string $game): array
    {
        return array_filter(
            $this->collections->toArray(),
            fn(Collection $c) => $c->getGame() === $game
        );
    }

    /**
     * Adiciona um deck
     */
    public function addDeck(Deck $deck): void
    {
        $this->decks->add($deck);
        $this->markAsUpdated();
    }

    /**
     * Remove um deck
     */
    public function removeDeck(Deck $deck): void
    {
        $this->decks->removeElement($deck);
        $this->markAsUpdated();
    }

    /**
     * @return Deck[]
     */
    public function getDecks(): array
    {
        return $this->decks->toArray();
    }

    /**
     * Retorna decks por jogo
     * 
     * @return Deck[]
     */
    public function getDecksByGame(string $game): array
    {
        return array_filter(
            $this->decks->toArray(),
            fn(Deck $d) => $d->getGame() === $game
        );
    }

    /**
     * Retorna estatísticas do inventário
     */
    public function getStatistics(): array
    {
        $stats = [
            'totalCards' => $this->getTotalCardCount(),
            'uniqueCards' => $this->getUniqueCardCount(),
            'collections' => count($this->collections),
            'decks' => count($this->decks),
            'byGame' => [],
        ];

        // Estatísticas por jogo
        foreach (['mtg', 'pokemon', 'onepiece'] as $game) {
            $gameCards = $this->getCardsByGame($game);
            $stats['byGame'][$game] = [
                'cards' => count($gameCards),
                'collections' => count($this->getCollectionsByGame($game)),
                'decks' => count($this->getDecksByGame($game)),
            ];
        }

        return $stats;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'userId' => $this->userId,
            'name' => $this->name,
            'description' => $this->description,
            'statistics' => $this->getStatistics(),
            'items' => array_map(fn($item) => $item->toArray(), $this->items->toArray()),
            'collections' => array_map(fn($c) => $c->toArray(), $this->collections->toArray()),
            'decks' => array_map(fn($d) => $d->toArray(), $this->decks->toArray()),
        ]);
    }
}
