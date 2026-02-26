<?php

namespace App\Domain\Entity;

use App\Core\Domain\Entity\BaseEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * DeckCard
 * 
 * Representa um card dentro de um deck com sua quantidade.
 */
#[ORM\Entity]
#[ORM\Table(name: "deck_cards")]
class DeckCard extends BaseEntity
{
    #[ORM\Id]
    #[ORM\Column(type: "uuid_string", unique: true)]
    protected mixed $id;
    #[ORM\ManyToOne(targetEntity: Deck::class, inversedBy: "cards")]
    #[ORM\JoinColumn(nullable: false)]
    private Deck $deck;

    #[ORM\ManyToOne(targetEntity: Card::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Card $card;

    #[ORM\Column(type: "integer")]
    private int $quantity;

    #[ORM\Column(length: 20)]
    private string $zone = 'main'; // 'main', 'sideboard'

    public function __construct(Deck $deck, Card $card, int $quantity = 1, string $zone = 'main')
    {
        parent::__construct();
        $this->deck = $deck;
        $this->card = $card;
        $this->quantity = max(1, $quantity);
        $this->zone = $zone;
    }

    public function getDeck(): Deck
    {
        return $this->deck;
    }

    public function getCard(): Card
    {
        return $this->card;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): void
    {
        $this->quantity = max(0, $quantity);
        $this->markAsUpdated();
    }

    public function getZone(): string
    {
        return $this->zone;
    }

    public function setZone(string $zone): void
    {
        $this->zone = $zone;
        $this->markAsUpdated();
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'card' => $this->card->toArray(),
            'quantity' => $this->quantity,
            'zone' => $this->zone,
        ]);
    }
}
