<?php

namespace App\Domain\Entity;

use App\Core\Domain\Entity\BaseEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * Card Entity (Abstract)
 * 
 * Classe base abstrata para todas as cartas de jogos.
 * Define propriedades e comportamentos comuns a todos os tipos de cards.
 */
#[ORM\Entity]
#[ORM\InheritanceType("SINGLE_TABLE")]
#[ORM\DiscriminatorColumn(name: "discr", type: "string")]
#[ORM\DiscriminatorMap([
    "mtg" => "CardMTG",
    "pokemon" => "CardPTCG",
    "onepiece" => "CardOPCG"
])]
abstract class Card extends BaseEntity
{
    #[ORM\Id]
    #[ORM\Column(type: "uuid_string", unique: true)]
    protected mixed $id;

    
    #[ORM\Column(length: 255)]
    protected string $name;

    #[ORM\Column(length: 20)]
    protected string $game; // 'mtg', 'pokemon', 'onepiece'

    #[ORM\Column(type: "text", nullable: true)]
    protected ?string $imageUrl = null;
    
    public function __construct(string $name, string $game)
    {
        parent::__construct();
        $this->name = $name;
        $this->game = $game;
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

    public function getGame(): string
    {
        return $this->game;
    }

    public function getImageUrl(): ?string
    {
        return $this->imageUrl;
    }

    public function setImageUrl(?string $imageUrl): void
    {
        $this->imageUrl = $imageUrl;
        $this->markAsUpdated();
    }

    /**
     * Retorna o tipo específico do card (deve ser implementado pelas classes filhas)
     */
    abstract public function getCardType(): string;

    /**
     * Valida se o card é válido (pode ser sobrescrito pelas classes filhas)
     */
    public function isValid(): bool
    {
        return !empty($this->name) && !empty($this->game);
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'name' => $this->name,
            'game' => $this->game,
            'imageUrl' => $this->imageUrl,
            'cardType' => $this->getCardType(),
        ]);
    }

    public function __toString(): string
    {
        return sprintf('%s (%s)', $this->name, $this->game);
    }
}
