<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\WorkerOnTraffic;
use Doctrine\Common\Collections\Criteria;

/**
 * @method WorkerOnTraffic|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method WorkerOnTraffic|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method WorkerOnTraffic[] findAll()
 * @method WorkerOnTraffic[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<WorkerOnTraffic>
 */
class WorkerOnTrafficRepository extends AbstractRepository
{

}