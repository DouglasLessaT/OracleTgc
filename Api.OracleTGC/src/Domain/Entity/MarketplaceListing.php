<?php

namespace App\Domain\Entity;

use App\Core\Domain\Entity\BaseEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * MarketplaceListing
 *
 * Representa um anúncio no marketplace (carta ou coleção à venda).
 * Fluxo: "Marketplace de cartas ou coleções de cartas".
 */
#[ORM\Entity]
#[ORM\Table(name: "marketplace_listings")]
class MarketplaceListing extends BaseEntity
{
    public const TYPE_CARD = 'card';
    public const TYPE_COLLECTION = 'collection';

    public const STATUS_ACTIVE = 'active';
    public const STATUS_SOLD = 'sold';
    public const STATUS_CANCELLED = 'cancelled';

    #[ORM\Id]
    #[ORM\Column(type: "uuid_string", unique: true)]
    protected mixed $id;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private User $seller;

    #[ORM\Column(length: 20)]
    private string $type; // card, collection

    #[ORM\ManyToOne(targetEntity: Card::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Card $card = null;

    #[ORM\ManyToOne(targetEntity: Collection::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Collection $collection = null;

    #[ORM\Column(type: "decimal", precision: 12, scale: 2)]
    private string $price;

    #[ORM\Column(length: 10)]
    private string $currency = 'BRL';

    #[ORM\Column(length: 20)]
    private string $status = self::STATUS_ACTIVE;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $description = null;

    public function __construct(User $seller, string $type, string $price)
    {
        parent::__construct();
        if (!in_array($type, [self::TYPE_CARD, self::TYPE_COLLECTION], true)) {
            throw new \InvalidArgumentException("Invalid listing type: {$type}");
        }
        $this->seller = $seller;
        $this->type = $type;
        $this->price = $price;
    }

    public function getSeller(): User
    {
        return $this->seller;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getCard(): ?Card
    {
        return $this->card;
    }

    public function setCard(?Card $card): void
    {
        $this->card = $card;
        $this->markAsUpdated();
    }

    public function getCollection(): ?Collection
    {
        return $this->collection;
    }

    public function setCollection(?Collection $collection): void
    {
        $this->collection = $collection;
        $this->markAsUpdated();
    }

    public function getPrice(): string
    {
        return $this->price;
    }

    public function setPrice(string $price): void
    {
        $this->price = $price;
        $this->markAsUpdated();
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): void
    {
        $this->currency = $currency;
        $this->markAsUpdated();
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        if (!in_array($status, [self::STATUS_ACTIVE, self::STATUS_SOLD, self::STATUS_CANCELLED], true)) {
            throw new \InvalidArgumentException("Invalid status: {$status}");
        }
        $this->status = $status;
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

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function toArray(): array
    {
        $data = array_merge(parent::toArray(), [
            'sellerId' => $this->seller->getId() instanceof \Symfony\Component\Uid\Uuid
            ? $this->seller->getId()->toRfc4122() : (string) $this->seller->getId(),
            'type' => $this->type,
            'price' => $this->price,
            'currency' => $this->currency,
            'status' => $this->status,
            'description' => $this->description,
        ]);
        $id = fn($e) => $e->getId() instanceof \Symfony\Component\Uid\Uuid ? $e->getId()->toRfc4122() : (string) $e->getId();
        if ($this->card) {
            $data['cardId'] = $id($this->card);
        }
        if ($this->collection) {
            $data['collectionId'] = $id($this->collection);
        }
        return $data;
    }
}
