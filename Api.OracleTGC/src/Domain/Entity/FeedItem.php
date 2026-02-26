<?php

namespace App\Domain\Entity;

use App\Core\Domain\Entity\BaseEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * FeedItem
 *
 * Item no feed de um usuário (fan-out on write).
 * Uma linha por (quem vê, referência ao conteúdo, ordem).
 * Permite leitura rápida do feed sem JOIN em posts/activities.
 */
#[ORM\Entity]
#[ORM\Table(name: "feed_items")]
#[ORM\Index(name: "idx_feed_items_user_created", columns: ["user_id", "created_at"])]
#[ORM\Index(name: "idx_feed_items_user_score", columns: ["user_id", "score"])]
class FeedItem extends BaseEntity
{
    #[ORM\Id]
    #[ORM\Column(type: "uuid_string", unique: true)]
    protected mixed $id;

    /** Quem vê este item no feed */
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: "user_id", referencedColumnName: "id", nullable: false, onDelete: "CASCADE")]
    private User $user;

    /** Tipo do item: post, activity */
    #[ORM\Column(length: 30)]
    private string $itemType;

    /** ID do post ou da activity */
    #[ORM\Column(type: "uuid_string")]
    private string $itemId;

    /** Score para ordenação por relevância (opcional) */
    #[ORM\Column(type: "float", options: ["default" => 0])]
    private float $score = 0.0;

    public function __construct(User $user, string $itemType, string $itemId, float $score = 0.0)
    {
        parent::__construct();
        $this->user = $user;
        $this->itemType = $itemType;
        $this->itemId = $itemId;
        $this->score = $score;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getItemType(): string
    {
        return $this->itemType;
    }

    public function getItemId(): string
    {
        return $this->itemId;
    }

    public function getScore(): float
    {
        return $this->score;
    }

    public function setScore(float $score): void
    {
        $this->score = $score;
        $this->markAsUpdated();
    }

    public function toArray(): array
    {
        $userId = $this->user->getId();
        $userIdStr = $userId instanceof \Symfony\Component\Uid\Uuid ? $userId->toRfc4122() : (string) $userId;

        return array_merge(parent::toArray(), [
            'userId' => $userIdStr,
            'itemType' => $this->itemType,
            'itemId' => $this->itemId,
            'score' => $this->score,
        ]);
    }
}
