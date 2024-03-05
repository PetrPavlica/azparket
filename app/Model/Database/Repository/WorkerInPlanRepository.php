<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\WorkerInPlan;

/**
 * @method WorkerInPlan|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method WorkerInPlan|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method WorkerInPlan[] findAll()
 * @method WorkerInPlan[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<Field>
 */
class WorkerInPlanRepository extends AbstractRepository
{

}