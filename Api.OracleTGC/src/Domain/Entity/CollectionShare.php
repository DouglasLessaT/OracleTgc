<?php

namespace App\Domain\Entity;

use App\Core\Domain\Entity\BaseEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * CollectionShare
 *
 * Representa o compartilhamento de uma coleção com outro usuário.
 * Fluxo: "Pode compartilhar suas coleções com outros usuários" (Free e Premium).
 */
#[ORM\Entity]
#[ORM\Table(name: "collection_shares")]
class CollectionShare extends BaseEntity
{
    public const PERMISSION_VIEW = 'view';
    public const PERMISSION_EDIT = 'edit';

    #[ORM\Id]
    #[ORM\Column(type: "uuid_string", unique: true)]
    protected mixed $id;

    #[ORM\ManyToOne(targetEntity: Collection::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Collection $collection;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: "shared_by_id", nullable: false)]
    private User $sharedBy;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: "shared_with_id", nullable: false)]
    private User $sharedWith;

    #[ORM\Column(length: 20)]
    private string $permission = self::PERMISSION_VIEW;

    public function __construct(Collection $collection, User $sharedBy, User $sharedWith, string $permission = self::PERMISSION_VIEW)
    {
        parent::__construct();
        $this->collection = $collection;
        $this->sharedBy = $sharedBy;
        $this->sharedWith = $sharedWith;
        $this->permission = $permission;
    }

    public function getCollection(): Collection
    {
        return $this->collection;
    }

    public function getSharedBy(): User
    {
        return $this->sharedBy;
    }

    public function getSharedWith(): User
    {
        return $this->sharedWith;
    }

    public function getPermission(): string
    {
        return $this->permission;
    }

    public function setPermission(string $permission): void
    {
        if (!in_array($permission, [self::PERMISSION_VIEW, self::PERMISSION_EDIT], true)) {
            throw new \InvalidArgumentException("Invalid permission: {$permission}");
        }
        $this->permission = $permission;
        $this->markAsUpdated();
    }

    public function canEdit(): bool
    {
        return $this->permission === self::PERMISSION_EDIT;
    }

    public function toArray(): array
    {
        $id = fn($entity) => $entity->getId() instanceof \Symfony\Component\Uid\Uuid
            ? $entity->getId()->toRfc4122() : (string) $entity->getId();
        return array_merge(parent::toArray(), [
            'collectionId' => $id($this->collection),
            'sharedById' => $id($this->sharedBy),
            'sharedWithId' => $id($this->sharedWith),
            'permission' => $this->permission,
        ]);
    }
}
