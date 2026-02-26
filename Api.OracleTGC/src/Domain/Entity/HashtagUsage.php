<?php

namespace App\Domain\Entity;

use App\Core\Domain\Entity\BaseEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * HashtagUsage
 *
 * Contagem ou score de uso de hashtag em um período (para trending).
 * Janela típica: 1h ou 24h (period_start = início da janela).
 */
#[ORM\Entity]
#[ORM\Table(name: "hashtag_usages")]
#[ORM\UniqueConstraint(name: "hashtag_usages_hashtag_period_unique", columns: ["hashtag_id", "period_start"])]
#[ORM\Index(name: "idx_hashtag_usages_period_score", columns: ["period_start", "score"])]
class HashtagUsage extends BaseEntity
{
    #[ORM\Id]
    #[ORM\Column(type: "uuid_string", unique: true)]
    protected mixed $id;

    #[ORM\ManyToOne(targetEntity: Hashtag::class)]
    #[ORM\JoinColumn(name: "hashtag_id", referencedColumnName: "id", nullable: false, onDelete: "CASCADE")]
    private Hashtag $hashtag;

    /** Início da janela de tempo (ex.: 2024-02-21 14:00:00 para janela de 1h) */
    #[ORM\Column(type: "datetime_immutable")]
    private \DateTimeImmutable $periodStart;

    /** Contagem de usos no período */
    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private int $count = 0;

    /** Score de tendência (opcional: pode combinar count + crescimento) */
    #[ORM\Column(type: "float", options: ["default" => 0])]
    private float $score = 0.0;

    public function __construct(Hashtag $hashtag, \DateTimeImmutable $periodStart, int $count = 0, float $score = 0.0)
    {
        parent::__construct();
        $this->hashtag = $hashtag;
        $this->periodStart = $periodStart;
        $this->count = $count;
        $this->score = $score;
    }

    public function getHashtag(): Hashtag
    {
        return $this->hashtag;
    }

    public function getPeriodStart(): \DateTimeImmutable
    {
        return $this->periodStart;
    }

    public function getCount(): int
    {
        return $this->count;
    }

    public function setCount(int $count): void
    {
        $this->count = $count;
        $this->markAsUpdated();
    }

    public function incrementCount(int $by = 1): void
    {
        $this->count += $by;
        $this->markAsUpdated();
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
        $hashtagId = $this->hashtag->getId();
        $hashtagIdStr = $hashtagId instanceof \Symfony\Component\Uid\Uuid ? $hashtagId->toRfc4122() : (string) $hashtagId;

        return array_merge(parent::toArray(), [
            'hashtagId' => $hashtagIdStr,
            'periodStart' => $this->periodStart->format('c'),
            'count' => $this->count,
            'score' => $this->score,
        ]);
    }
}
