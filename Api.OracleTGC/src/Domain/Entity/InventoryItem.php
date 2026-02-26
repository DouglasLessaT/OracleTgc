<?php

namespace App\Domain\Entity;

use App\Core\Domain\Entity\BaseEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * InventoryItem
 * 
 * Representa um item individual no inventário.
 * Associa um card com quantidade e metadados adicionais.
 */
#[ORM\Entity]
#[ORM\Table(name: "inventory_items")]
class InventoryItem extends BaseEntity
{
    #[ORM\Id]
    #[ORM\Column(type: "uuid_string", unique: true)]
    protected mixed $id;
    #[ORM\ManyToOne(targetEntity: Inventory::class, inversedBy: "items")]
    #[ORM\JoinColumn(nullable: false)]
    private Inventory $inventory;

    #[ORM\ManyToOne(targetEntity: Card::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Card $card;

    #[ORM\Column(type: "integer")]
    private int $quantity;

    #[ORM\Column(type: "json", nullable: true)]
    private ?array $metadata = null; // Dados extras como condição, idioma, etc

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $condition = null; // Near Mint, Lightly Played, etc

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $language = null;

    #[ORM\Column]
    private bool $isFoil = false;

    #[ORM\Column(type: "float", nullable: true)]
    private ?float $purchasePrice = null;

    #[ORM\Column(type: "float", nullable: true)]
    private ?float $currentPrice = null;

    public function __construct(
        Inventory $inventory,
        Card $card,
        int $quantity = 1,
        ?array $metadata = null
    ) {
        parent::__construct();
        $this->inventory = $inventory;
        $this->card = $card;
        $this->quantity = max(1, $quantity);
        $this->metadata = $metadata ?? [];
    }

    public function getInventory(): Inventory
    {
        return $this->inventory;
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

    public function incrementQuantity(int $amount = 1): void
    {
        $this->quantity += $amount;
        $this->markAsUpdated();
    }

    public function decrementQuantity(int $amount = 1): void
    {
        $this->quantity = max(0, $this->quantity - $amount);
        $this->markAsUpdated();
    }

    public function getMetadata(): ?array
    {
        return $this->metadata;
    }

    public function setMetadata(?array $metadata): void
    {
        $this->metadata = $metadata;
        $this->markAsUpdated();
    }

    public function getCondition(): ?string
    {
        return $this->condition;
    }

    public function setCondition(?string $condition): void
    {
        $this->condition = $condition;
        $this->markAsUpdated();
    }

    public function getLanguage(): ?string
    {
        return $this->language;
    }

    public function setLanguage(?string $language): void
    {
        $this->language = $language;
        $this->markAsUpdated();
    }

    public function isFoil(): bool
    {
        return $this->isFoil;
    }

    public function setIsFoil(bool $isFoil): void
    {
        $this->isFoil = $isFoil;
        $this->markAsUpdated();
    }

    public function getPurchasePrice(): ?float
    {
        return $this->purchasePrice;
    }

    public function setPurchasePrice(?float $purchasePrice): void
    {
        $this->purchasePrice = $purchasePrice;
        $this->markAsUpdated();
    }

    public function getCurrentPrice(): ?float
    {
        return $this->currentPrice;
    }

    public function setCurrentPrice(?float $currentPrice): void
    {
        $this->currentPrice = $currentPrice;
        $this->markAsUpdated();
    }

    /**
     * Calcula o valor total do item (quantidade * preço atual)
     */
    public function getTotalValue(): ?float
    {
        return $this->currentPrice ? $this->currentPrice * $this->quantity : null;
    }

    /**
     * Calcula o lucro/prejuízo
     */
    public function getProfitLoss(): ?float
    {
        if (!$this->purchasePrice || !$this->currentPrice) {
            return null;
        }

        return ($this->currentPrice - $this->purchasePrice) * $this->quantity;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'card' => $this->card->toArray(),
            'quantity' => $this->quantity,
            'condition' => $this->condition,
            'language' => $this->language,
            'isFoil' => $this->isFoil,
            'purchasePrice' => $this->purchasePrice,
            'currentPrice' => $this->currentPrice,
            'totalValue' => $this->getTotalValue(),
            'profitLoss' => $this->getProfitLoss(),
            'metadata' => $this->metadata,
        ]);
    }
}
