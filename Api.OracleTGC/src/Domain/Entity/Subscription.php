<?php

namespace App\Domain\Entity;

use App\Core\Domain\Entity\BaseEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * Subscription
 *
 * Inscrição de um usuário em um canal.
 */
#[ORM\Entity]
#[ORM\Table(name: "subscriptions")]
#[ORM\UniqueConstraint(name: "subscriptions_user_channel_unique", columns: ["user_id", "channel_id"])]
#[ORM\Index(name: "idx_subscriptions_user", columns: ["user_id"])]
#[ORM\Index(name: "idx_subscriptions_channel", columns: ["channel_id"])]
class Subscription extends BaseEntity
{
    #[ORM\Id]
    #[ORM\Column(type: "uuid_string", unique: true)]
    protected mixed $id;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: "user_id", referencedColumnName: "id", nullable: false, onDelete: "CASCADE")]
    private User $user;

    #[ORM\ManyToOne(targetEntity: Channel::class)]
    #[ORM\JoinColumn(name: "channel_id", referencedColumnName: "id", nullable: false, onDelete: "CASCADE")]
    private Channel $channel;

    public function __construct(User $user, Channel $channel)
    {
        parent::__construct();
        $this->user = $user;
        $this->channel = $channel;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getChannel(): Channel
    {
        return $this->channel;
    }

    public function toArray(): array
    {
        $userId = $this->user->getId();
        $channelId = $this->channel->getId();
        $userIdStr = $userId instanceof \Symfony\Component\Uid\Uuid ? $userId->toRfc4122() : (string) $userId;
        $channelIdStr = $channelId instanceof \Symfony\Component\Uid\Uuid ? $channelId->toRfc4122() : (string) $channelId;

        return array_merge(parent::toArray(), [
            'userId' => $userIdStr,
            'channelId' => $channelIdStr,
        ]);
    }
}
