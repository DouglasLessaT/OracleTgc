<?php

namespace App\Domain\Entity;

use App\Core\Domain\Entity\BaseEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * Activity
 *
 * Evento de ação para montar feed (timeline).
 * Ex.: "Fulano adicionou carta X na coleção Y", "Fulano criou o deck Z".
 */
#[ORM\Entity]
#[ORM\Table(name: "activities")]
#[ORM\Index(name: "idx_activities_user_created", columns: ["user_id", "created_at"])]
class Activity extends BaseEntity
{
    public const TYPE_ADDED_CARD = 'added_card';
    public const TYPE_CREATED_COLLECTION = 'created_collection';
    public const TYPE_CREATED_DECK = 'created_deck';
    public const TYPE_UPDATED_COLLECTION = 'updated_collection';
    public const TYPE_UPDATED_DECK = 'updated_deck';
    public const TYPE_POST = 'post';

    #[ORM\Id]
    #[ORM\Column(type: "uuid_string", unique: true)]
    protected mixed $id;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: "user_id", referencedColumnName: "id", nullable: false, onDelete: "CASCADE")]
    private User $user;

    #[ORM\Column(length: 50)]
    private string $type;

    /** Entidade alvo: post, collection, deck, card, etc. */
    #[ORM\Column(length: 50)]
    private string $targetType;

    #[ORM\Column(type: "uuid_string")]
    private string $targetId;

    #[ORM\Column(type: "json", nullable: true)]
    private ?array $metadata = null;

    public function __construct(User $user, string $type, string $targetType, string $targetId, ?array $metadata = null)
    {
        parent::__construct();
        $this->user = $user;
        $this->type = $type;
        $this->targetType = $targetType;
        $this->targetId = $targetId;
        $this->metadata = $metadata ?? [];
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getTargetType(): string
    {
        return $this->targetType;
    }

    public function getTargetId(): string
    {
        return $this->targetId;
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

    public function toArray(): array
    {
        $userId = $this->user->getId();
        $userIdStr = $userId instanceof \Symfony\Component\Uid\Uuid ? $userId->toRfc4122() : (string) $userId;

        return array_merge(parent::toArray(), [
            'userId' => $userIdStr,
            'type' => $this->type,
            'targetType' => $this->targetType,
            'targetId' => $this->targetId,
            'metadata' => $this->metadata,
        ]);
    }
}
