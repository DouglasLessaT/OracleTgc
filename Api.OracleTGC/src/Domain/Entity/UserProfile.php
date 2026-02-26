<?php

namespace App\Domain\Entity;

use App\Core\Domain\Entity\BaseEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * UserProfile
 *
 * Perfil público do usuário. Mantém a tabela users enxuta (auth/identidade)
 * e concentra aqui dados de exibição e contadores sociais.
 * Relação 1:1 com User (user_id único).
 */
#[ORM\Entity]
#[ORM\Table(name: "user_profiles")]
#[ORM\UniqueConstraint(name: "user_profiles_user_id_unique", columns: ["user_id"])]
class UserProfile extends BaseEntity
{
    #[ORM\Id]
    #[ORM\Column(type: "uuid_string", unique: true)]
    protected mixed $id;

    #[ORM\OneToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: "user_id", referencedColumnName: "id", nullable: false, unique: true, onDelete: "CASCADE")]
    private User $user;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $avatarUrl = null;

    /** Nome de exibição (se null, usar User.name) */
    #[ORM\Column(length: 100, nullable: true)]
    private ?string $displayName = null;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $bio = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $website = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $location = null;

    /** Contador desnormalizado: quantos usuários seguem este perfil */
    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private int $followersCount = 0;

    /** Contador desnormalizado: quantos usuários este perfil segue */
    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private int $followingCount = 0;

    public function __construct(User $user)
    {
        parent::__construct();
        $this->user = $user;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getAvatarUrl(): ?string
    {
        return $this->avatarUrl;
    }

    public function setAvatarUrl(?string $avatarUrl): void
    {
        $this->avatarUrl = $avatarUrl;
        $this->markAsUpdated();
    }

    public function getDisplayName(): ?string
    {
        return $this->displayName;
    }

    public function setDisplayName(?string $displayName): void
    {
        $this->displayName = $displayName;
        $this->markAsUpdated();
    }

    public function getBio(): ?string
    {
        return $this->bio;
    }

    public function setBio(?string $bio): void
    {
        $this->bio = $bio;
        $this->markAsUpdated();
    }

    public function getWebsite(): ?string
    {
        return $this->website;
    }

    public function setWebsite(?string $website): void
    {
        $this->website = $website;
        $this->markAsUpdated();
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location): void
    {
        $this->location = $location;
        $this->markAsUpdated();
    }

    public function getFollowersCount(): int
    {
        return $this->followersCount;
    }

    public function setFollowersCount(int $followersCount): void
    {
        $this->followersCount = $followersCount;
        $this->markAsUpdated();
    }

    public function incrementFollowersCount(): void
    {
        $this->followersCount++;
        $this->markAsUpdated();
    }

    public function decrementFollowersCount(): void
    {
        if ($this->followersCount > 0) {
            $this->followersCount--;
            $this->markAsUpdated();
        }
    }

    public function getFollowingCount(): int
    {
        return $this->followingCount;
    }

    public function setFollowingCount(int $followingCount): void
    {
        $this->followingCount = $followingCount;
        $this->markAsUpdated();
    }

    public function incrementFollowingCount(): void
    {
        $this->followingCount++;
        $this->markAsUpdated();
    }

    public function decrementFollowingCount(): void
    {
        if ($this->followingCount > 0) {
            $this->followingCount--;
            $this->markAsUpdated();
        }
    }

    public function toArray(): array
    {
        $userId = $this->user->getId();
        $userIdStr = $userId instanceof \Symfony\Component\Uid\Uuid ? $userId->toRfc4122() : (string) $userId;

        return array_merge(parent::toArray(), [
            'userId' => $userIdStr,
            'avatarUrl' => $this->avatarUrl,
            'displayName' => $this->displayName,
            'bio' => $this->bio,
            'website' => $this->website,
            'location' => $this->location,
            'followersCount' => $this->followersCount,
            'followingCount' => $this->followingCount,
        ]);
    }
}
