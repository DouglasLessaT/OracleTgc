<?php

namespace App\Domain\Entity;

use App\Core\Domain\Entity\BaseEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * Notification
 *
 * Notificação para um usuário (like, comment, follow, mention, etc.).
 */
#[ORM\Entity]
#[ORM\Table(name: "notifications")]
#[ORM\Index(name: "idx_notifications_user_created", columns: ["user_id", "created_at"])]
class Notification extends BaseEntity
{
    public const TYPE_FOLLOW = 'follow';
    public const TYPE_LIKE = 'like';
    public const TYPE_COMMENT = 'comment';
    public const TYPE_MENTION = 'mention';
    public const TYPE_POST = 'post';
    public const TYPE_ACTIVITY = 'activity';

    #[ORM\Id]
    #[ORM\Column(type: "uuid_string", unique: true)]
    protected mixed $id;

    /** Quem recebe a notificação */
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: "user_id", referencedColumnName: "id", nullable: false, onDelete: "CASCADE")]
    private User $user;

    #[ORM\Column(length: 50)]
    private string $type;

    /** Quem fez a ação (autor) */
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: "actor_id", referencedColumnName: "id", nullable: true, onDelete: "SET NULL")]
    private ?User $actor = null;

    /** Alvo da ação: post, comment, collection, etc. */
    #[ORM\Column(length: 50, nullable: true)]
    private ?string $targetType = null;

    #[ORM\Column(type: "uuid_string", nullable: true)]
    private ?string $targetId = null;

    #[ORM\Column(type: "datetime_immutable", nullable: true)]
    private ?\DateTimeImmutable $readAt = null;

    #[ORM\Column(type: "json", nullable: true)]
    private ?array $extra = null;

    public function __construct(
        User $user,
        string $type,
        ?User $actor = null,
        ?string $targetType = null,
        ?string $targetId = null,
        ?array $extra = null
    ) {
        parent::__construct();
        $this->user = $user;
        $this->type = $type;
        $this->actor = $actor;
        $this->targetType = $targetType;
        $this->targetId = $targetId;
        $this->extra = $extra ?? [];
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getActor(): ?User
    {
        return $this->actor;
    }

    public function setActor(?User $actor): void
    {
        $this->actor = $actor;
        $this->markAsUpdated();
    }

    public function getTargetType(): ?string
    {
        return $this->targetType;
    }

    public function getTargetId(): ?string
    {
        return $this->targetId;
    }

    public function getReadAt(): ?\DateTimeImmutable
    {
        return $this->readAt;
    }

    public function markAsRead(): void
    {
        $this->readAt = new \DateTimeImmutable();
        $this->markAsUpdated();
    }

    public function isRead(): bool
    {
        return $this->readAt !== null;
    }

    public function getExtra(): ?array
    {
        return $this->extra;
    }

    public function setExtra(?array $extra): void
    {
        $this->extra = $extra;
        $this->markAsUpdated();
    }

    public function toArray(): array
    {
        $userId = $this->user->getId();
        $userIdStr = $userId instanceof \Symfony\Component\Uid\Uuid ? $userId->toRfc4122() : (string) $userId;
        $actorId = $this->actor?->getId();
        $actorIdStr = $actorId instanceof \Symfony\Component\Uid\Uuid ? $actorId->toRfc4122() : ($actorId ? (string) $actorId : null);

        return array_merge(parent::toArray(), [
            'userId' => $userIdStr,
            'type' => $this->type,
            'actorId' => $actorIdStr,
            'targetType' => $this->targetType,
            'targetId' => $this->targetId,
            'readAt' => $this->readAt?->format('c'),
            'extra' => $this->extra,
        ]);
    }
}
