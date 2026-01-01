<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\CountryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CountryRepository::class)]
#[ORM\Table(name: 'countries')]
#[ORM\Index(columns: ['abr'], name: 'idx_country_abr')]
#[ORM\Index(columns: ['iso'], name: 'idx_country_iso')]
class Country
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $name;

    #[ORM\Column(length: 255)]
    private string $nameUrl;

    #[ORM\Column(length: 3)]
    private string $abr;

    #[ORM\Column(length: 2)]
    private string $iso;

    /**
     * @var Collection<int, Player>
     */
    #[ORM\OneToMany(targetEntity: Player::class, mappedBy: 'country')]
    private Collection $players;

    public function __construct()
    {
        $this->players = new ArrayCollection();
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

    public function getAbr(): string
    {
        return $this->abr;
    }

    public function setAbr(string $abr): void
    {
        $this->abr = strtoupper($abr);
    }

    public function getIso(): string
    {
        return $this->iso;
    }

    public function setIso(string $iso): void
    {
        $this->iso = strtolower($iso);
    }

    /**
     * @return Collection<int, Player>
     */
    public function getPlayers(): Collection
    {
        return $this->players;
    }

    public function addPlayer(Player $player): void
    {
        if (!$this->players->contains($player)) {
            $this->players->add($player);
            $player->setCountry($this);
        }
    }

    public function removePlayer(Player $player): void
    {
        if ($this->players->removeElement($player)) {
            if ($player->getCountry() === $this) {
                $player->setCountry(null);
            }
        }
    }
}
