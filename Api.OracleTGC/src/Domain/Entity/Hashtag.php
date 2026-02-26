<?php

namespace App\Domain\Entity;

use App\Core\Domain\Entity\BaseEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * Hashtag
 *
 * Termo de tendência (ex.: #mtg, #pokemon). HashtagUsage armazena contagem por período.
 */
#[ORM\Entity]
#[ORM\Table(name: "hashtags")]
#[ORM\UniqueConstraint(name: "hashtags_name_unique", columns: ["name"])]
class Hashtag extends BaseEntity
{
    #[ORM\Id]
    #[ORM\Column(type: "uuid_string", unique: true)]
    protected mixed $id;

    /** Nome normalizado (ex.: mtg, sem #) */
    #[ORM\Column(length: 100)]
    private string $name;

    /** Slug para URL (ex.: mtg) */
    #[ORM\Column(length: 100)]
    private string $slug;

    public function __construct(string $name)
    {
        parent::__construct();
        $this->name = $name;
        $slug = strtolower(trim((string) preg_replace('/[^a-z0-9]+/i', '-', trim($name)), '-'));
        $this->slug = $slug !== '' ? $slug : $name;
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

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'name' => $this->name,
            'slug' => $this->slug,
        ]);
    }
}
