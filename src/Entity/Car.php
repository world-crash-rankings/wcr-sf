<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\CarRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CarRepository::class)]
#[ORM\Table(name: 'cars')]
class Car
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $name;

    /**
     * @var Collection<int, Strat>
     */
    #[ORM\ManyToMany(targetEntity: Strat::class, mappedBy: 'cars')]
    private Collection $strats;

    /**
     * @var Collection<int, Score>
     */
    #[ORM\OneToMany(targetEntity: Score::class, mappedBy: 'car')]
    private Collection $scores;

    public function __construct()
    {
        $this->strats = new ArrayCollection();
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
            $strat->addCar($this);
        }
    }

    public function removeStrat(Strat $strat): void
    {
        if ($this->strats->removeElement($strat)) {
            $strat->removeCar($this);
        }
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
            $score->setCar($this);
        }
    }

    public function removeScore(Score $score): void
    {
        if ($this->scores->removeElement($score)) {
            if ($score->getCar() === $this) {
                $score->setCar(null);
            }
        }
    }
}
