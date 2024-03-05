<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\Service;
use Doctrine\Common\Collections\Criteria;

/**
 * @method Service|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method Service|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method Service[] findAll()
 * @method Service[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<Service>
 */
class ServiceRepository extends AbstractRepository
{

    /**
     * @param \DateTimeImmutable $date
     * @return Service
     */
    public function searchByDate(\DateTimeImmutable $date)
    {
        $midNight = $date->setTime(0, 0);
        $service = $this->_em->getRepository(Service::class)
            ->createQueryBuilder('s')
            ->where("s.dateService >= '" . $midNight->format('Y-m-d') . "'")
            ->andWhere("s.dateService < '" . $midNight->modify('1day')->format('Y-m-d') . "'")
            ->getQuery()
            ->getOneOrNullResult();

        return $service;
    }
}