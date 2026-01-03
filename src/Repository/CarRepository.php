<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Car;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Car>
 */
class CarRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Car::class);
    }

    /**
     * Get all cars as an associative array (id => name)
     *
     * @return array<int, string>
     */
    public function getCarList(): array
    {
        $cars = $this->createQueryBuilder('c')
            ->orderBy('c.name', 'ASC')
            ->getQuery()
            ->getResult();

        $carList = [];
        foreach ($cars as $car) {
            $carList[$car->getId()] = $car->getName();
        }

        return $carList;
    }
}
