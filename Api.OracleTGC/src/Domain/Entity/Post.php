<?php

namespace App\Domain\Entity;

use App\Core\Domain\Entity\BaseEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * Post
 *
 * Representa um post do usuário no perfil.
 * Fluxo: "Posts do usuário" na tela de Perfil.
 */
#[ORM\Entity]
#[ORM\Table(name: "posts")]
class Post extends BaseEntity
{
    #[ORM\Id]
    #[ORM\Column(type: "uuid_string", unique: true)]
    protected mixed $id;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private User $author;

    #[ORM\Column(type: "text")]
    private string $content;

    #[ORM\Column(type: "string", length: 50, nullable: true)]
    private ?string $type = null; // ex: text, card_highlight, collection_share

    #[ORM\Column(type: "json", nullable: true)]
    private ?array $metadata = null; // ex: card_id, collection_id para referência

    public function __construct(User $author, string $content, ?string $type = null, ?array $metadata = null)
    {
        parent::__construct();
        $this->author = $author;
        $this->content = $content;
        $this->type = $type;
        $this->metadata = $metadata ?? [];
    }

    public function getAuthor(): User
    {
        return $this->author;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
        $this->markAsUpdated();
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): void
    {
        $this->type = $type;
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

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'authorId' => $this->author->getId() instanceof \Symfony\Component\Uid\Uuid
                ? $this->author->getId()->toRfc4122() : (string) $this->author->getId(),
            'content' => $this->content,
            'type' => $this->type,
            'metadata' => $this->metadata,
        ]);
    }
}
