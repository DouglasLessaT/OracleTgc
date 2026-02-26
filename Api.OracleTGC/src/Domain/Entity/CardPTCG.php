<?php

namespace App\Domain\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CardPTCG - Pokémon Trading Card Game Card
 * 
 * Representa uma carta de Pokémon TCG.
 */
#[ORM\Entity]
class CardPTCG extends Card
{
    #[ORM\Column(length: 50)]
    private string $setId;

    #[ORM\Column(length: 255)]
    private string $setName;

    #[ORM\Column(length: 20)]
    private string $number;

    #[ORM\Column(length: 50)]
    private string $rarity; // common, uncommon, rare, etc

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $supertype = null; // Pokémon, Trainer, Energy

    #[ORM\Column(type: "json")]
    private array $subtypes = [];

    #[ORM\Column(type: "integer", nullable: true)]
    private ?int $hp = null;

    #[ORM\Column(type: "json")]
    private array $types = []; // Grass, Fire, Water, etc

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $evolvesFrom = null;

    #[ORM\Column(type: "json")]
    private array $attacks = [];

    #[ORM\Column(type: "json")]
    private array $weaknesses = [];

    #[ORM\Column(type: "json")]
    private array $resistances = [];

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $retreatCost = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $artist = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $nationalPokedexNumber = null;

    #[ORM\Column(type: "float", nullable: true)]
    private ?float $priceUsd = null;

    #[ORM\Column(type: "float", nullable: true)]
    private ?float $priceEur = null;

    public function __construct(
        string $name,
        string $setId,
        string $setName,
        string $number,
        string $rarity
    ) {
        parent::__construct($name, 'pokemon');
        $this->setId = $setId;
        $this->setName = $setName;
        $this->number = $number;
        $this->rarity = $rarity;
    }

    public function getCardType(): string
    {
        return 'pokemon';
    }

    // Getters and Setters

    public function getSetId(): string
    {
        return $this->setId;
    }

    public function setSetId(string $setId): void
    {
        $this->setId = $setId;
        $this->markAsUpdated();
    }

    public function getSetName(): string
    {
        return $this->setName;
    }

    public function setSetName(string $setName): void
    {
        $this->setName = $setName;
        $this->markAsUpdated();
    }

    public function getNumber(): string
    {
        return $this->number;
    }

    public function setNumber(string $number): void
    {
        $this->number = $number;
        $this->markAsUpdated();
    }

    public function getRarity(): string
    {
        return $this->rarity;
    }

    public function setRarity(string $rarity): void
    {
        $this->rarity = $rarity;
        $this->markAsUpdated();
    }

    public function getSupertype(): ?string
    {
        return $this->supertype;
    }

    public function setSupertype(?string $supertype): void
    {
        $this->supertype = $supertype;
        $this->markAsUpdated();
    }

    public function getSubtypes(): array
    {
        return $this->subtypes;
    }

    public function setSubtypes(array $subtypes): void
    {
        $this->subtypes = $subtypes;
        $this->markAsUpdated();
    }

    public function getHp(): ?int
    {
        return $this->hp;
    }

    public function setHp(?int $hp): void
    {
        $this->hp = $hp;
        $this->markAsUpdated();
    }

    public function getTypes(): array
    {
        return $this->types;
    }

    public function setTypes(array $types): void
    {
        $this->types = $types;
        $this->markAsUpdated();
    }

    public function getEvolvesFrom(): ?string
    {
        return $this->evolvesFrom;
    }

    public function setEvolvesFrom(?string $evolvesFrom): void
    {
        $this->evolvesFrom = $evolvesFrom;
        $this->markAsUpdated();
    }

    public function getAttacks(): array
    {
        return $this->attacks;
    }

    public function setAttacks(array $attacks): void
    {
        $this->attacks = $attacks;
        $this->markAsUpdated();
    }

    public function getWeaknesses(): array
    {
        return $this->weaknesses;
    }

    public function setWeaknesses(array $weaknesses): void
    {
        $this->weaknesses = $weaknesses;
        $this->markAsUpdated();
    }

    public function getResistances(): array
    {
        return $this->resistances;
    }

    public function setResistances(array $resistances): void
    {
        $this->resistances = $resistances;
        $this->markAsUpdated();
    }

    public function getRetreatCost(): ?string
    {
        return $this->retreatCost;
    }

    public function setRetreatCost(?string $retreatCost): void
    {
        $this->retreatCost = $retreatCost;
        $this->markAsUpdated();
    }

    public function getArtist(): ?string
    {
        return $this->artist;
    }

    public function setArtist(?string $artist): void
    {
        $this->artist = $artist;
        $this->markAsUpdated();
    }

    public function getNationalPokedexNumber(): ?string
    {
        return $this->nationalPokedexNumber;
    }

    public function setNationalPokedexNumber(?string $nationalPokedexNumber): void
    {
        $this->nationalPokedexNumber = $nationalPokedexNumber;
        $this->markAsUpdated();
    }

    public function getPriceUsd(): ?float
    {
        return $this->priceUsd;
    }

    public function setPriceUsd(?float $priceUsd): void
    {
        $this->priceUsd = $priceUsd;
        $this->markAsUpdated();
    }

    public function getPriceEur(): ?float
    {
        return $this->priceEur;
    }

    public function setPriceEur(?float $priceEur): void
    {
        $this->priceEur = $priceEur;
        $this->markAsUpdated();
    }

    /**
     * Verifica se é um Pokémon
     */
    public function isPokemon(): bool
    {
        return $this->supertype === 'Pokémon';
    }

    /**
     * Verifica se é um Trainer
     */
    public function isTrainer(): bool
    {
        return $this->supertype === 'Trainer';
    }

    /**
     * Verifica se é uma Energy
     */
    public function isEnergy(): bool
    {
        return $this->supertype === 'Energy';
    }

    /**
     * Verifica se é uma evolução
     */
    public function isEvolution(): bool
    {
        return !empty($this->evolvesFrom);
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'setId' => $this->setId,
            'setName' => $this->setName,
            'number' => $this->number,
            'rarity' => $this->rarity,
            'supertype' => $this->supertype,
            'subtypes' => $this->subtypes,
            'hp' => $this->hp,
            'types' => $this->types,
            'evolvesFrom' => $this->evolvesFrom,
            'attacks' => $this->attacks,
            'weaknesses' => $this->weaknesses,
            'resistances' => $this->resistances,
            'retreatCost' => $this->retreatCost,
            'artist' => $this->artist,
            'nationalPokedexNumber' => $this->nationalPokedexNumber,
            'prices' => [
                'usd' => $this->priceUsd,
                'eur' => $this->priceEur,
            ],
        ]);
    }
}
