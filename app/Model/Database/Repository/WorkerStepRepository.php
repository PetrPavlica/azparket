<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\WorkerStep;

/**
 * @method WorkerStep|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method WorkerStep|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method WorkerStep[] findAll()
 * @method WorkerStep[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<WorkerStep>
 */
class WorkerStepRepository extends AbstractRepository
{

}