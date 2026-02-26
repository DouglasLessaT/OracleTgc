<?php

namespace App\Domain\Entity;

use App\Core\Domain\Entity\BaseEntity;
use App\Core\Domain\Exception\ValidationException;
use Doctrine\ORM\Mapping as ORM;

/**
 * Deck
 * 
 * Representa um deck de cards para jogar.
 * Cada jogo tem suas próprias regras de construção de deck.
 */
#[ORM\Entity]
#[ORM\Table(name: "decks")]
class Deck extends BaseEntity
{
    #[ORM\Id]
    #[ORM\Column(type: "uuid_string", unique: true)]
    protected mixed $id;

    #[ORM\ManyToOne(targetEntity: Inventory::class, inversedBy: "decks")]
    #[ORM\JoinColumn(nullable: false)]
    private Inventory $inventory;

    #[ORM\Column(length: 255)]
    private string $name;

    #[ORM\Column(length: 20)]
    private string $game; // mtg, pokemon, onepiece

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $format = null; // Standard, Modern, Commander, etc
    
    /** @var Collection<int, DeckCard> */
    #[ORM\OneToMany(mappedBy: "deck", targetEntity: DeckCard::class, cascade: ["persist", "remove"], orphanRemoval: true)]
    private \Doctrine\Common\Collections\Collection $cards;
    
    #[ORM\Column]
    private bool $isLegal = false;

    #[ORM\Column(type: "json", nullable: true)]
    private ?array $validationErrors = null;

    public function __construct(string $name, string $game, ?string $format = null)
    {
        parent::__construct();
        $this->name = $name;
        $this->game = $game;
        $this->format = $format;
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

    public function getFormat(): ?string
    {
        return $this->format;
    }

    public function setFormat(?string $format): void
    {
        $this->format = $format;
        $this->markAsUpdated();
    }

    /**
     * Adiciona um card ao deck principal
     */
    public function addCardToMain(Card $card, int $quantity = 1): void
    {
        $this->addCard($card, $quantity, 'main');
    }

    /**
     * Adiciona um card ao sideboard
     */
    public function addCardToSideboard(Card $card, int $quantity = 1): void
    {
        $this->addCard($card, $quantity, 'sideboard');
    }

    /**
     * Adiciona um card ao deck
     */
    private function addCard(Card $card, int $quantity, string $zone): void
    {
        // Verifica se o card já existe na zona
        foreach ($this->cards as $deckCard) {
            if ($deckCard->getZone() === $zone && $deckCard->getCard()->equals($card)) {
                $deckCard->setQuantity($deckCard->getQuantity() + $quantity);
                $this->markAsUpdated();
                return;
            }
        }

        // Adiciona novo card
        $this->cards->add(new DeckCard($this, $card, $quantity, $zone));
        $this->markAsUpdated();
    }

    /**
     * Remove um card do deck principal
     */
    public function removeCardFromMain(Card $card): void
    {
        $this->removeCard($card, 'main');
    }

    /**
     * Remove um card do sideboard
     */
    public function removeCardFromSideboard(Card $card): void
    {
        $this->removeCard($card, 'sideboard');
    }

    private function removeCard(Card $card, string $zone): void
    {
        foreach ($this->cards as $deckCard) {
            if ($deckCard->getZone() === $zone && $deckCard->getCard()->equals($card)) {
                $this->cards->removeElement($deckCard);
                $this->markAsUpdated();
                return;
            }
        }
    }

    /**
     * @return DeckCard[]
     */
    public function getMainDeck(): array
    {
        return $this->cards->filter(fn(DeckCard $dc) => $dc->getZone() === 'main')->toArray();
    }

    /**
     * @return DeckCard[]
     */
    public function getSideboard(): array
    {
        return $this->cards->filter(fn(DeckCard $dc) => $dc->getZone() === 'sideboard')->toArray();
    }

    /**
     * Retorna o total de cards no deck principal
     */
    public function getMainDeckCount(): int
    {
        return array_reduce(
            $this->getMainDeck(),
            fn(int $sum, DeckCard $dc) => $sum + $dc->getQuantity(),
            0
        );
    }

    /**
     * Retorna o total de cards no sideboard
     */
    public function getSideboardCount(): int
    {
        return array_reduce(
            $this->getSideboard(),
            fn(int $sum, DeckCard $dc) => $sum + $dc->getQuantity(),
            0
        );
    }

    /**
     * Valida o deck de acordo com as regras do jogo/formato
     */
    public function validate(): bool
    {
        $this->validationErrors = [];

        switch ($this->game) {
            case 'mtg':
                $this->validateMTG();
                break;
            case 'pokemon':
                $this->validatePokemon();
                break;
            case 'onepiece':
                $this->validateOnePiece();
                break;
        }

        $this->isLegal = empty($this->validationErrors);
        return $this->isLegal;
    }

    /**
     * Valida deck de Magic
     */
    private function validateMTG(): void
    {
        $mainCount = $this->getMainDeckCount();
        $sideCount = $this->getSideboardCount();

        // Regras básicas
        if ($this->format === 'Commander') {
            if ($mainCount !== 100) {
                $this->validationErrors[] = "Commander deck must have exactly 100 cards (has {$mainCount})";
            }
        } else {
            if ($mainCount < 60) {
                $this->validationErrors[] = "Main deck must have at least 60 cards (has {$mainCount})";
            }
        }

        if ($sideCount > 15) {
            $this->validationErrors[] = "Sideboard cannot have more than 15 cards (has {$sideCount})";
        }

        // Limite de 4 cópias (exceto terrenos básicos)
        foreach ($this->getMainDeck() as $deckCard) {
            if ($deckCard->getQuantity() > 4) {
                $cardName = $deckCard->getCard()->getName();
                // Verificar se é terreno básico (simplificado)
                if (!in_array($cardName, ['Plains', 'Island', 'Swamp', 'Mountain', 'Forest'])) {
                    $this->validationErrors[] = "Cannot have more than 4 copies of {$cardName}";
                }
            }
        }
    }

    /**
     * Valida deck de Pokémon
     */
    private function validatePokemon(): void
    {
        $mainCount = $this->getMainDeckCount();

        if ($mainCount !== 60) {
            $this->validationErrors[] = "Pokémon deck must have exactly 60 cards (has {$mainCount})";
        }

        // Limite de 4 cópias (exceto energia básica)
        foreach ($this->getMainDeck() as $deckCard) {
            if ($deckCard->getQuantity() > 4) {
                $card = $deckCard->getCard();
                if ($card instanceof CardPTCG) {
                    if ($card->getSupertype() !== 'Energy' || !empty($card->getSubtypes())) {
                        $this->validationErrors[] = "Cannot have more than 4 copies of {$card->getName()}";
                    }
                }
            }
        }
    }

    /**
     * Valida deck de One Piece
     */
    private function validateOnePiece(): void
    {
        $mainCount = $this->getMainDeckCount();

        if ($mainCount !== 50) {
            $this->validationErrors[] = "One Piece deck must have exactly 50 cards (has {$mainCount})";
        }

        // Deve ter exatamente 1 Leader
        $leaderCount = 0;
        foreach ($this->getMainDeck() as $deckCard) {
            $card = $deckCard->getCard();
            if ($card instanceof CardOPCG && $card->isLeader()) {
                $leaderCount += $deckCard->getQuantity();
            }
        }

        if ($leaderCount !== 1) {
            $this->validationErrors[] = "Deck must have exactly 1 Leader card (has {$leaderCount})";
        }

        // Limite de 4 cópias
        foreach ($this->getMainDeck() as $deckCard) {
            if ($deckCard->getQuantity() > 4) {
                $this->validationErrors[] = "Cannot have more than 4 copies of {$deckCard->getCard()->getName()}";
            }
        }
    }

    public function isLegal(): bool
    {
        return $this->isLegal;
    }

    public function setIsLegal(bool $isLegal): void
    {
        $this->isLegal = $isLegal;
        $this->markAsUpdated();
    }

    public function getValidationErrors(): ?array
    {
        return $this->validationErrors;
    }

    public function setValidationErrors(?array $validationErrors): void
    {
        $this->validationErrors = $validationErrors;
        $this->markAsUpdated();
    }

    /**
     * Exporta o deck em formato texto
     */
    public function exportToText(): string
    {
        $output = "// {$this->name}\n";
        $output .= "// Game: {$this->game}\n";
        if ($this->format) {
            $output .= "// Format: {$this->format}\n";
        }
        $output .= "\n";

        $output .= "Main Deck ({$this->getMainDeckCount()} cards)\n";
        foreach ($this->getMainDeck() as $deckCard) {
            $output .= "{$deckCard->getQuantity()} {$deckCard->getCard()->getName()}\n";
        }

        $sideboard = $this->getSideboard();
        if (!empty($sideboard)) {
            $output .= "\nSideboard ({$this->getSideboardCount()} cards)\n";
            foreach ($sideboard as $deckCard) {
                $output .= "{$deckCard->getQuantity()} {$deckCard->getCard()->getName()}\n";
            }
        }

        return $output;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'name' => $this->name,
            'game' => $this->game,
            'description' => $this->description,
            'format' => $this->format,
            'mainDeckCount' => $this->getMainDeckCount(),
            'sideboardCount' => $this->getSideboardCount(),
            'isLegal' => $this->isLegal,
            'validationErrors' => $this->validationErrors,
            'mainDeck' => array_map(fn($dc) => $dc->toArray(), $this->getMainDeck()),
            'sideboard' => array_map(fn($dc) => $dc->toArray(), $this->getSideboard()),
        ]);
    }
}
