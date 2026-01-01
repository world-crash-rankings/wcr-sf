<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\PlayerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PlayerRepository::class)]
#[ORM\Table(name: 'players')]
class Player
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $name;

    #[ORM\Column(length: 255)]
    private string $nameUrl;

    #[ORM\ManyToOne(targetEntity: Country::class, inversedBy: 'players')]
    #[ORM\JoinColumn(nullable: false)]
    private Country $country;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notes = null;

    #[ORM\Column(type: Types::BIGINT, nullable: true)]
    private ?int $total = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 3, nullable: true)]
    private ?string $avgPos = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 3, scale: 1, nullable: true)]
    private ?string $avgStars = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2, nullable: true)]
    private ?string $avgPercent = null;

    #[ORM\Column(nullable: true)]
    private ?int $totalRank = null;

    #[ORM\Column(nullable: true)]
    private ?int $avgPosRank = null;

    #[ORM\Column(nullable: true)]
    private ?int $avgStarsRank = null;

    #[ORM\Column(nullable: true)]
    private ?int $avgPercentRank = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $wcrChannel = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $personnalChannel = null;

    #[ORM\Column(nullable: true)]
    private ?bool $xblTotal = null;

    /**
     * @var Collection<int, Score>
     */
    #[ORM\OneToMany(targetEntity: Score::class, mappedBy: 'player')]
    private Collection $scores;

    public function __construct()
    {
        $this->scores = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
        $this->nameUrl = str_replace(' ', '-', $name);
    }

    public function getNameUrl(): string
    {
        return $this->nameUrl;
    }

    public function setNameUrl(string $nameUrl): void
    {
        $this->nameUrl = $nameUrl;
    }

    public function getCountry(): Country
    {
        return $this->country;
    }

    public function setCountry(?Country $country): void
    {
        if ($country !== null) {
            $this->country = $country;
        }
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): void
    {
        $this->notes = $notes;
    }

    public function getTotal(): ?int
    {
        return $this->total;
    }

    public function setTotal(?int $total): void
    {
        $this->total = $total;
    }

    public function getAvgPos(): ?string
    {
        return $this->avgPos;
    }

    public function setAvgPos(?string $avgPos): void
    {
        $this->avgPos = $avgPos;
    }

    public function getAvgStars(): ?string
    {
        return $this->avgStars;
    }

    public function setAvgStars(?string $avgStars): void
    {
        $this->avgStars = $avgStars;
    }

    public function getAvgPercent(): ?string
    {
        return $this->avgPercent;
    }

    public function setAvgPercent(?string $avgPercent): void
    {
        $this->avgPercent = $avgPercent;
    }

    public function getTotalRank(): ?int
    {
        return $this->totalRank;
    }

    public function setTotalRank(?int $totalRank): void
    {
        $this->totalRank = $totalRank;
    }

    public function getAvgPosRank(): ?int
    {
        return $this->avgPosRank;
    }

    public function setAvgPosRank(?int $avgPosRank): void
    {
        $this->avgPosRank = $avgPosRank;
    }

    public function getAvgStarsRank(): ?int
    {
        return $this->avgStarsRank;
    }

    public function setAvgStarsRank(?int $avgStarsRank): void
    {
        $this->avgStarsRank = $avgStarsRank;
    }

    public function getAvgPercentRank(): ?int
    {
        return $this->avgPercentRank;
    }

    public function setAvgPercentRank(?int $avgPercentRank): void
    {
        $this->avgPercentRank = $avgPercentRank;
    }

    public function getWcrChannel(): ?string
    {
        return $this->wcrChannel;
    }

    public function setWcrChannel(?string $wcrChannel): void
    {
        $this->wcrChannel = $wcrChannel;
    }

    public function getPersonnalChannel(): ?string
    {
        return $this->personnalChannel;
    }

    public function setPersonnalChannel(?string $personnalChannel): void
    {
        $this->personnalChannel = $personnalChannel;
    }

    public function isXblTotal(): ?bool
    {
        return $this->xblTotal;
    }

    public function setXblTotal(?bool $xblTotal): void
    {
        $this->xblTotal = $xblTotal;
    }

    /**
     * @return Collection<int, Score>
     */
    public function getScores(): Collection
    {
        return $this->scores;
    }

    public function addScore(Score $score): void
    {
        if (!$this->scores->contains($score)) {
            $this->scores->add($score);
            $score->setPlayer($this);
        }
    }

    public function removeScore(Score $score): void
    {
        if ($this->scores->removeElement($score)) {
            if ($score->getPlayer() === $this) {
                $score->setPlayer(null);
            }
        }
    }
}
