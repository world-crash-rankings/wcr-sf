<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Player;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Player>
 */
class PlayerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Player::class);
    }

    public function findByNameUrl(string $nameUrl): ?Player
    {
        return $this->findOneBy(['nameUrl' => $nameUrl]);
    }

    /**
     * @return Player[]
     */
    public function findByTotalRank(int $limit): array
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.country', 'c')
            ->addSelect('c')
            ->where('p.totalRank <= :limit')
            ->setParameter('limit', $limit)
            ->orderBy('p.total', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Player[]
     */
    public function findByAvgPosRank(int $limit): array
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.country', 'c')
            ->addSelect('c')
            ->where('p.avgPosRank <= :limit')
            ->setParameter('limit', $limit)
            ->orderBy('p.avgPos', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Player[]
     */
    public function findByAvgStarsRank(int $limit): array
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.country', 'c')
            ->addSelect('c')
            ->where('p.avgStarsRank <= :limit')
            ->setParameter('limit', $limit)
            ->orderBy('p.avgStars', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Player[]
     */
    public function findByAvgPercentRank(int $limit): array
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.country', 'c')
            ->addSelect('c')
            ->where('p.avgPercentRank <= :limit')
            ->setParameter('limit', $limit)
            ->orderBy('p.avgPercent', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Player[]
     */
    public function findByCountryOrderedByRank(int $countryId): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.country = :countryId')
            ->andWhere('p.avgPosRank IS NOT NULL')
            ->setParameter('countryId', $countryId)
            ->orderBy('p.avgPosRank', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get all players ordered by name
     *
     * @return Player[]
     */
    public function findAllOrderedByName(): array
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.country', 'c')
            ->addSelect('c')
            ->orderBy('p.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get all players as choice array for forms (name => nameUrl)
     *
     * @return array<string, string>
     */
    public function getPlayerChoices(): array
    {
        $players = $this->createQueryBuilder('p')
            ->orderBy('p.name', 'ASC')
            ->getQuery()
            ->getResult();

        $choices = [];
        foreach ($players as $player) {
            $choices[$player->getName()] = $player->getNameUrl();
        }

        return $choices;
    }

    /**
     * Get distinct platforms used by a player
     *
     * @return string[]
     */
    public function getPlayerPlatforms(int $playerId): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = '
            SELECT DISTINCT(platform) as platform
            FROM scores
            WHERE player_id = :playerId
              AND platform IS NOT NULL
            ORDER BY platform DESC
        ';

        $result = $conn->executeQuery($sql, ['playerId' => $playerId]);
        return array_column($result->fetchAllAssociative(), 'platform');
    }
}
