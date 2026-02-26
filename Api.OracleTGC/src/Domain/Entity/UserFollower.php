<?php

namespace App\Domain\Entity;

use App\Core\Domain\Entity\BaseEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * UserFollower
 *
 * Representa a relação de seguidor entre usuários (perfil).
 * Fluxo: "Seguidores do usuário" na tela de Perfil.
 */
#[ORM\Entity]
#[ORM\Table(name: "user_followers")]
#[ORM\UniqueConstraint(name: "user_follower_unique", columns: ["follower_id", "following_id"])]
class UserFollower extends BaseEntity
{
    #[ORM\Id]
    #[ORM\Column(type: "uuid_string", unique: true)]
    protected mixed $id;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: "follower_id", nullable: false)]
    private User $follower;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: "following_id", nullable: false)]
    private User $following;

    public function __construct(User $follower, User $following)
    {
        parent::__construct();
        $this->follower = $follower;
        $this->following = $following;
    }

    public function getFollower(): User
    {
        return $this->follower;
    }

    public function getFollowing(): User
    {
        return $this->following;
    }

    public function toArray(): array
    {
        $id = fn($u) => $u->getId() instanceof \Symfony\Component\Uid\Uuid ? $u->getId()->toRfc4122() : (string) $u->getId();
        return array_merge(parent::toArray(), [
            'followerId' => $id($this->follower),
            'followingId' => $id($this->following),
        ]);
    }
}
