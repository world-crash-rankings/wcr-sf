<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\Frequency;
use App\Enum\GlitchType;
use App\Enum\Platform;
use App\Enum\ProofType;
use App\Enum\Version;
use App\Repository\ScoreRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ScoreRepository::class)]
#[ORM\Table(name: 'scores')]
#[ORM\Index(columns: ['player_id', 'zone_id'], name: 'idx_player_zone')]
#[ORM\Index(columns: ['zone_id', 'score'], name: 'idx_zone_score')]
class Score
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Player::class, inversedBy: 'scores')]
    #[ORM\JoinColumn(nullable: false)]
    private Player $player;

    #[ORM\ManyToOne(targetEntity: Zone::class, inversedBy: 'scores')]
    #[ORM\JoinColumn(nullable: false)]
    private Zone $zone;

    #[ORM\ManyToOne(targetEntity: Car::class, inversedBy: 'scores')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Car $car = null;

    #[ORM\ManyToOne(targetEntity: Strat::class, inversedBy: 'scores')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Strat $strat = null;

    /**
     * @var string
     */
    #[ORM\Column(type: Types::BIGINT, options: ['unsigned' => true])]
    private string $score;

    /**
     * @var string|null
     */
    #[ORM\Column(type: Types::BIGINT, nullable: true, options: ['unsigned' => true])]
    private ?string $damage = null;

    /**
     * @var string|null
     */
    #[ORM\Column(type: Types::BIGINT, nullable: true, options: ['unsigned' => true])]
    private ?string $multi = null;

    #[ORM\Column(options: ['default' => false])]
    private bool $formerWr = false;

    #[ORM\Column(options: ['default' => false])]
    private bool $prEntry = false;

    #[ORM\Column(options: ['default' => false])]
    private bool $emulator = false;

    #[ORM\Column(type: Types::INTEGER, nullable: true, options: ['unsigned' => true])]
    private ?int $chartRank = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true, options: ['unsigned' => true])]
    private ?int $bestRank = null;

    #[ORM\Column(type: Types::STRING, length: 20, enumType: ProofType::class, nullable: true)]
    private ?ProofType $proofType = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $proofLink = null;

    #[ORM\Column(type: Types::STRING, length: 10, enumType: Platform::class, nullable: true)]
    private ?Platform $platform = null;

    #[ORM\Column(type: Types::STRING, length: 10, enumType: Version::class, nullable: true)]
    private ?Version $version = null;

    #[ORM\Column(type: Types::STRING, length: 10, enumType: Frequency::class, nullable: true)]
    private ?Frequency $freq = null;

    #[ORM\Column(type: Types::STRING, length: 10, enumType: GlitchType::class, nullable: true)]
    private ?GlitchType $glitch = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2, nullable: true)]
    private ?string $percentWr = null;

    #[ORM\Column(nullable: true)]
    private ?int $stars = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private \DateTimeInterface $registration;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $realisation = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $inaccurate = null;

    public function __construct()
    {
        $this->registration = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPlayer(): Player
    {
        return $this->player;
    }

    public function setPlayer(?Player $player): void
    {
        if ($player !== null) {
            $this->player = $player;
        }
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

    public function getCar(): ?Car
    {
        return $this->car;
    }

    public function setCar(?Car $car): void
    {
        $this->car = $car;
    }

    public function getStrat(): ?Strat
    {
        return $this->strat;
    }

    public function setStrat(?Strat $strat): void
    {
        $this->strat = $strat;
    }

    public function getScore(): string
    {
        return $this->score;
    }

    public function setScore(string $score): void
    {
        $this->score = $score;
    }

    public function getDamage(): ?string
    {
        return $this->damage;
    }

    public function setDamage(?string $damage): void
    {
        $this->damage = $damage;
    }

    public function getMulti(): ?string
    {
        return $this->multi;
    }

    public function setMulti(?string $multi): void
    {
        $this->multi = $multi;
    }

    public function isFormerWr(): bool
    {
        return $this->formerWr;
    }

    public function setFormerWr(bool $formerWr): void
    {
        $this->formerWr = $formerWr;
    }

    public function isPrEntry(): bool
    {
        return $this->prEntry;
    }

    public function setPrEntry(bool $prEntry): void
    {
        $this->prEntry = $prEntry;
    }

    public function isEmulator(): bool
    {
        return $this->emulator;
    }

    public function setEmulator(bool $emulator): void
    {
        $this->emulator = $emulator;
    }

    public function getChartRank(): ?int
    {
        return $this->chartRank;
    }

    public function setChartRank(?int $chartRank): void
    {
        $this->chartRank = $chartRank;
    }

    public function getBestRank(): ?int
    {
        return $this->bestRank;
    }

    public function setBestRank(?int $bestRank): void
    {
        $this->bestRank = $bestRank;
    }

    public function getProofType(): ?ProofType
    {
        return $this->proofType;
    }

    public function setProofType(?ProofType $proofType): void
    {
        $this->proofType = $proofType;
    }

    public function getProofLink(): ?string
    {
        return $this->proofLink;
    }

    public function setProofLink(?string $proofLink): void
    {
        $this->proofLink = $proofLink;
    }

    public function getPlatform(): ?Platform
    {
        return $this->platform;
    }

    public function setPlatform(?Platform $platform): void
    {
        $this->platform = $platform;
    }

    public function getVersion(): ?Version
    {
        return $this->version;
    }

    public function setVersion(?Version $version): void
    {
        $this->version = $version;
    }

    public function getFreq(): ?Frequency
    {
        return $this->freq;
    }

    public function setFreq(?Frequency $freq): void
    {
        $this->freq = $freq;
    }

    public function getGlitch(): ?GlitchType
    {
        return $this->glitch;
    }

    public function setGlitch(?GlitchType $glitch): void
    {
        $this->glitch = $glitch;
    }

    public function getPercentWr(): ?string
    {
        return $this->percentWr;
    }

    public function setPercentWr(?string $percentWr): void
    {
        $this->percentWr = $percentWr;
    }

    public function getStars(): ?int
    {
        return $this->stars;
    }

    public function setStars(?int $stars): void
    {
        $this->stars = $stars;
    }

    public function getRegistration(): \DateTimeInterface
    {
        return $this->registration;
    }

    public function setRegistration(\DateTimeInterface $registration): void
    {
        $this->registration = $registration;
    }

    public function getRealisation(): ?\DateTimeInterface
    {
        return $this->realisation;
    }

    public function setRealisation(?\DateTimeInterface $realisation): void
    {
        $this->realisation = $realisation;
    }

    public function getInaccurate(): ?string
    {
        return $this->inaccurate;
    }

    public function setInaccurate(?string $inaccurate): void
    {
        $this->inaccurate = $inaccurate;
    }
}
