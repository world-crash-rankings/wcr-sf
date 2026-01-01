<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\StratRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StratRepository::class)]
#[ORM\Table(name: 'strats')]
class Strat
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $name;

    #[ORM\ManyToOne(targetEntity: Zone::class, inversedBy: 'strats')]
    #[ORM\JoinColumn(nullable: false)]
    private Zone $zone;

    #[ORM\Column(type: Types::TEXT)]
    private string $description;

    #[ORM\Column(nullable: true)]
    private ?bool $hz50 = null;

    #[ORM\Column(nullable: true)]
    private ?bool $hz60 = null;

    #[ORM\Column(nullable: true)]
    private ?bool $gc = null;

    #[ORM\Column(nullable: true)]
    private ?bool $xbox = null;

    #[ORM\Column(nullable: true)]
    private ?bool $ps2 = null;

    #[ORM\Column]
    private int $bestDamage;

    #[ORM\Column]
    private int $bestMulti;

    #[ORM\Column]
    private int $bestTotal;

    /**
     * @var Collection<int, Car>
     */
    #[ORM\ManyToMany(targetEntity: Car::class, inversedBy: 'strats')]
    #[ORM\JoinTable(name: 'strats_cars')]
    private Collection $cars;

    /**
     * @var Collection<int, Score>
     */
    #[ORM\OneToMany(targetEntity: Score::class, mappedBy: 'strat')]
    private Collection $scores;

    public function __construct()
    {
        $this->cars = new ArrayCollection();
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

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function isHz50(): ?bool
    {
        return $this->hz50;
    }

    public function setHz50(?bool $hz50): void
    {
        $this->hz50 = $hz50;
    }

    public function isHz60(): ?bool
    {
        return $this->hz60;
    }

    public function setHz60(?bool $hz60): void
    {
        $this->hz60 = $hz60;
    }

    public function isGc(): ?bool
    {
        return $this->gc;
    }

    public function setGc(?bool $gc): void
    {
        $this->gc = $gc;
    }

    public function isXbox(): ?bool
    {
        return $this->xbox;
    }

    public function setXbox(?bool $xbox): void
    {
        $this->xbox = $xbox;
    }

    public function isPs2(): ?bool
    {
        return $this->ps2;
    }

    public function setPs2(?bool $ps2): void
    {
        $this->ps2 = $ps2;
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

    /**
     * @return Collection<int, Car>
     */
    public function getCars(): Collection
    {
        return $this->cars;
    }

    public function addCar(Car $car): void
    {
        if (!$this->cars->contains($car)) {
            $this->cars->add($car);
        }
    }

    public function removeCar(Car $car): void
    {
        $this->cars->removeElement($car);
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
            $score->setStrat($this);
        }
    }

    public function removeScore(Score $score): void
    {
        if ($this->scores->removeElement($score)) {
            if ($score->getStrat() === $this) {
                $score->setStrat(null);
            }
        }
    }
}
