<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\WorkerPosition;

/**
 * @method WorkerPosition|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method WorkerPosition|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method WorkerPosition[] findAll()
 * @method WorkerPosition[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<WorkerPosition>
 */
class WorkerPositionRepository extends AbstractRepository
{

}