<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Country;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Country>
 */
class CountryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Country::class);
    }

    public function findByAbr(string $abr): ?Country
    {
        return $this->findOneBy(['abr' => strtoupper($abr)]);
    }

    public function findByIso(string $iso): ?Country
    {
        return $this->findOneBy(['iso' => strtolower($iso)]);
    }
}
