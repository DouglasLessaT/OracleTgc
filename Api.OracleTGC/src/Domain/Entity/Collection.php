<?php

namespace App\Domain\Entity;

use App\Core\Domain\Entity\BaseEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * Collection
 * 
 * Representa uma coleção de cards organizada por critérios específicos.
 * Exemplo: "Coleção Base Set Pokémon", "Coleção Innistrad MTG"
 */
#[ORM\Entity]
#[ORM\Table(name: "collections")]
class Collection extends BaseEntity
{
    #[ORM\Id]
    #[ORM\Column(type: "uuid_string", unique: true)]
    protected mixed $id;

    #[ORM\ManyToOne(targetEntity: Inventory::class, inversedBy: "collections")]
    #[ORM\JoinColumn(nullable: false)]
    private Inventory $inventory;

    #[ORM\Column(length: 255)]
    private string $name;

    #[ORM\Column(length: 20)]
    private string $game; // mtg, pokemon, onepiece

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $setCode = null; // Código do set (opcional)

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $setName = null;
    
    /** @var \Doctrine\Common\Collections\Collection<int, Card> */
    #[ORM\ManyToMany(targetEntity: Card::class)]
    #[ORM\JoinTable(name: "collection_cards")]
    private \Doctrine\Common\Collections\Collection $cards;
    
    #[ORM\Column(type: "integer", nullable: true)]
    private ?int $targetCount = null; // Meta de cards para completar

    #[ORM\Column]
    private bool $isComplete = false;

    /** Coleção padrão "all cards" do jogo (Free: apenas uma por jogo) */
    #[ORM\Column(options: ["default" => false])]
    private bool $isDefaultAllCards = false;

    /** Marcada como deck de jogo (Premium) */
    #[ORM\Column(options: ["default" => false])]
    private bool $isGameDeck = false;

    public function __construct(string $name, string $game)
    {
        parent::__construct();
        $this->name = $name;
        $this->game = $game;
        $this->cards = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function setInventory(Inventory $inventory): void
    {
        $this->inventory = $inventory;
        $this->markAsUpdated();
    }

    public function getInventory(): Inventory
    {
        return $this->inventory;
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

    public function getGame(): string
    {
        return $this->game;
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

    public function getSetCode(): ?string
    {
        return $this->setCode;
    }

    public function setSetCode(?string $setCode): void
    {
        $this->setCode = $setCode;
        $this->markAsUpdated();
    }

    public function getSetName(): ?string
    {
        return $this->setName;
    }

    public function setSetName(?string $setName): void
    {
        $this->setName = $setName;
        $this->markAsUpdated();
    }

    /**
     * Adiciona um card à coleção
     */
    public function addCard(Card $card): void
    {
        // Verifica se o card já existe
        if ($this->cards->contains($card)) {
            return; // Já existe
        }

        $this->cards->add($card);
        $this->updateCompletionStatus();
        $this->markAsUpdated();
    }

    /**
     * Remove um card da coleção
     */
    public function removeCard(Card $card): void
    {
        $this->cards->removeElement($card);
        $this->updateCompletionStatus();
        $this->markAsUpdated();
    }

    /**
     * @return Card[]
     */
    public function getCards(): array
    {
        return $this->cards->toArray();
    }

    /**
     * Retorna a quantidade de cards na coleção
     */
    public function getCardCount(): int
    {
        return count($this->cards);
    }

    public function getTargetCount(): ?int
    {
        return $this->targetCount;
    }

    public function setTargetCount(?int $targetCount): void
    {
        $this->targetCount = $targetCount;
        $this->updateCompletionStatus();
        $this->markAsUpdated();
    }

    public function isComplete(): bool
    {
        return $this->isComplete;
    }

    /**
     * Atualiza o status de completude
     */
    private function updateCompletionStatus(): void
    {
        if ($this->targetCount !== null) {
            $this->isComplete = $this->getCardCount() >= $this->targetCount;
        }
    }

    /**
     * Retorna o progresso da coleção (0-100)
     */
    public function getCompletionPercentage(): ?float
    {
        if ($this->targetCount === null || $this->targetCount === 0) {
            return null;
        }

        return min(100, ($this->getCardCount() / $this->targetCount) * 100);
    }

    /**
     * Verifica se um card está na coleção
     */
    public function hasCard(Card $card): bool
    {
        return $this->cards->contains($card);
    }

    public function isDefaultAllCards(): bool
    {
        return $this->isDefaultAllCards;
    }

    public function setIsDefaultAllCards(bool $isDefaultAllCards): void
    {
        $this->isDefaultAllCards = $isDefaultAllCards;
        $this->markAsUpdated();
    }

    public function isGameDeck(): bool
    {
        return $this->isGameDeck;
    }

    public function setIsGameDeck(bool $isGameDeck): void
    {
        $this->isGameDeck = $isGameDeck;
        $this->markAsUpdated();
    }

    /**
     * Retorna cards faltantes (se houver uma lista de referência)
     */
    public function getMissingCards(array $referenceCards): array
    {
        return array_filter(
            $referenceCards,
            fn(Card $card) => !$this->hasCard($card)
        );
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'name' => $this->name,
            'game' => $this->game,
            'description' => $this->description,
            'setCode' => $this->setCode,
            'setName' => $this->setName,
            'cardCount' => $this->getCardCount(),
            'targetCount' => $this->targetCount,
            'isComplete' => $this->isComplete,
            'completionPercentage' => $this->getCompletionPercentage(),
            'isDefaultAllCards' => $this->isDefaultAllCards,
            'isGameDeck' => $this->isGameDeck,
            'cards' => array_map(fn($card) => $card->toArray(), $this->getCards()),
        ]);
    }
}
