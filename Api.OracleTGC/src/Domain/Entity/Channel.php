<?php

namespace App\Domain\Entity;

use App\Core\Domain\Entity\BaseEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * Channel
 *
 * Canal temático (jogo, set, assunto). Usuários podem se inscrever (Subscription).
 * Dono opcional: null = canal do sistema (ex.: "MTG", "Pokémon").
 */
#[ORM\Entity]
#[ORM\Table(name: "channels")]
#[ORM\UniqueConstraint(name: "channels_slug_unique", columns: ["slug"])]
class Channel extends BaseEntity
{
    public const TYPE_GAME = 'game';
    public const TYPE_SET = 'set';
    public const TYPE_TOPIC = 'topic';

    #[ORM\Id]
    #[ORM\Column(type: "uuid_string", unique: true)]
    protected mixed $id;

    /** Dono do canal (null = canal do sistema) */
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: "owner_id", referencedColumnName: "id", nullable: true, onDelete: "CASCADE")]
    private ?User $owner = null;

    #[ORM\Column(length: 255)]
    private string $name;

    #[ORM\Column(length: 100)]
    private string $slug;

    #[ORM\Column(length: 50)]
    private string $type = self::TYPE_TOPIC;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $description = null;

    /** Contador desnormalizado de inscritos */
    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private int $subscribersCount = 0;

    public function __construct(string $name, string $slug, string $type = self::TYPE_TOPIC, ?User $owner = null)
    {
        parent::__construct();
        $this->name = $name;
        $this->slug = $slug;
        $this->type = $type;
        $this->owner = $owner;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): void
    {
        $this->owner = $owner;
        $this->markAsUpdated();
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

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): void
    {
        $this->slug = $slug;
        $this->markAsUpdated();
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
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

    public function getSubscribersCount(): int
    {
        return $this->subscribersCount;
    }

    public function setSubscribersCount(int $subscribersCount): void
    {
        $this->subscribersCount = $subscribersCount;
        $this->markAsUpdated();
    }

    public function incrementSubscribersCount(): void
    {
        $this->subscribersCount++;
        $this->markAsUpdated();
    }

    public function decrementSubscribersCount(): void
    {
        if ($this->subscribersCount > 0) {
            $this->subscribersCount--;
            $this->markAsUpdated();
        }
    }

    public function toArray(): array
    {
        $ownerId = $this->owner?->getId();
        $ownerIdStr = $ownerId instanceof \Symfony\Component\Uid\Uuid ? $ownerId->toRfc4122() : ($ownerId ? (string) $ownerId : null);

        return array_merge(parent::toArray(), [
            'ownerId' => $ownerIdStr,
            'name' => $this->name,
            'slug' => $this->slug,
            'type' => $this->type,
            'description' => $this->description,
            'subscribersCount' => $this->subscribersCount,
        ]);
    }
}
