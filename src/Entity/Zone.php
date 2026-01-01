<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ZoneRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ZoneRepository::class)]
#[ORM\Table(name: 'zones')]
class Zone
{
    #[ORM\Id]
    #[ORM\Column]
    private int $id;

    #[ORM\Column(length: 255)]
    private string $name;

    #[ORM\Column(nullable: true)]
    private ?bool $ps2 = null;

    #[ORM\Column(nullable: true)]
    private ?bool $dmgWrKnown = null;

    #[ORM\Column(type: Types::TEXT)]
    private string $description;

    #[ORM\Column]
    private int $bestDamage;

    #[ORM\Column]
    private int $bestMulti;

    #[ORM\Column]
    private int $bestTotal;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $glitch = null;

    #[ORM\Column(length: 255)]
    private string $top25Channel;

    #[ORM\Column(length: 255)]
    private string $bestVidsChannel;

    /**
     * @var array<string, mixed>
     */
    #[ORM\Column(type: Types::JSON)]
    private array $stars = [];

    #[ORM\Column(length: 255)]
    private string $forum;

    /**
     * @var Collection<int, Score>
     */
    #[ORM\OneToMany(targetEntity: Score::class, mappedBy: 'zone')]
    private Collection $scores;

    /**
     * @var Collection<int, Strat>
     */
    #[ORM\OneToMany(targetEntity: Strat::class, mappedBy: 'zone')]
    private Collection $strats;

    /**
     * @var Collection<int, Star>
     */
    #[ORM\OneToMany(targetEntity: Star::class, mappedBy: 'zone')]
    private Collection $starThresholds;

    public function __construct()
    {
        $this->scores = new ArrayCollection();
        $this->strats = new ArrayCollection();
        $this->starThresholds = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function isPs2(): ?bool
    {
        return $this->ps2;
    }

    public function setPs2(?bool $ps2): void
    {
        $this->ps2 = $ps2;
    }

    public function isDmgWrKnown(): ?bool
    {
        return $this->dmgWrKnown;
    }

    public function setDmgWrKnown(?bool $dmgWrKnown): void
    {
        $this->dmgWrKnown = $dmgWrKnown;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getBestDamage(): int
    {
        return $this->bestDamage;
    }

    public function setBestDamage(int $bestDamage): void
    {
        $this->bestDamage = $bestDamage;
    }

    public function getBestMulti(): int
    {
        return $this->bestMulti;
    }

    public function setBestMulti(int $bestMulti): void
    {
        $this->bestMulti = $bestMulti;
    }

    public function getBestTotal(): int
    {
        return $this->bestTotal;
    }

    public function setBestTotal(int $bestTotal): void
    {
        $this->bestTotal = $bestTotal;
    }

    public function getGlitch(): ?string
    {
        return $this->glitch;
    }

    public function setGlitch(?string $glitch): void
    {
        $this->glitch = $glitch;
    }

    public function getTop25Channel(): string
    {
        return $this->top25Channel;
    }

    public function setTop25Channel(string $top25Channel): void
    {
        $this->top25Channel = $top25Channel;
    }

    public function getBestVidsChannel(): string
    {
        return $this->bestVidsChannel;
    }

    public function setBestVidsChannel(string $bestVidsChannel): void
    {
        $this->bestVidsChannel = $bestVidsChannel;
    }

    /**
     * @return array<string, mixed>
     */
    public function getStars(): array
    {
        return $this->stars;
    }

    /**
     * @param array<string, mixed> $stars
     */
    public function setStars(array $stars): void
    {
        $this->stars = $stars;
    }

    public function getForum(): string
    {
        return $this->forum;
    }

    public function setForum(string $forum): void
    {
        $this->forum = $forum;
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
            $score->setZone($this);
        }
    }

    public function removeScore(Score $score): void
    {
        if ($this->scores->removeElement($score)) {
            if ($score->getZone() === $this) {
                $score->setZone(null);
            }
        }
    }

    /**
     * @return Collection<int, Strat>
     */
    public function getStrats(): Collection
    {
        return $this->strats;
    }

    public function addStrat(Strat $strat): void
    {
        if (!$this->strats->contains($strat)) {
            $this->strats->add($strat);
            $strat->setZone($this);
        }
    }

    public function removeStrat(Strat $strat): void
    {
        if ($this->strats->removeElement($strat)) {
            if ($strat->getZone() === $this) {
                $strat->setZone(null);
            }
        }
    }

    /**
     * @return Collection<int, Star>
     */
    public function getStarThresholds(): Collection
    {
        return $this->starThresholds;
    }

    public function addStarThreshold(Star $starThreshold): void
    {
        if (!$this->starThresholds->contains($starThreshold)) {
            $this->starThresholds->add($starThreshold);
            $starThreshold->setZone($this);
        }
    }

    public function removeStarThreshold(Star $starThreshold): void
    {
        if ($this->starThresholds->removeElement($starThreshold)) {
            if ($starThreshold->getZone() === $this) {
                $starThreshold->setZone(null);
            }
        }
    }
}
