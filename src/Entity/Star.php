<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\StarRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StarRepository::class)]
#[ORM\Table(name: 'stars')]
#[ORM\UniqueConstraint(name: 'UNIQ_ZONE_NBSTARS', columns: ['zone_id', 'nb_stars'])]
class Star
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Zone::class, inversedBy: 'starThresholds')]
    #[ORM\JoinColumn(nullable: false)]
    private Zone $zone;

    #[ORM\Column(options: ['unsigned' => true])]
    private int $nbStars;

    /**
     * @var string
     */
    #[ORM\Column(type: Types::BIGINT, options: ['unsigned' => true])]
    private string $score;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getZone(): Zone
    {
        return $this->zone;
    }

    public function setZone(?Zone $zone): void
    {
        if ($zone !== null) {
            $this->zone = $zone;
        }
    }

    public function getNbStars(): int
    {
        return $this->nbStars;
    }

    public function setNbStars(int $nbStars): void
    {
        $this->nbStars = $nbStars;
    }

    public function getScore(): string
    {
        return $this->score;
    }

    public function setScore(string $score): void
    {
        $this->score = $score;
    }
}
