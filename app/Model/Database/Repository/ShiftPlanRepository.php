<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\ShiftPlan;

/**
 * @method ShiftPlan|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method ShiftPlan|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method ShiftPlan[] findAll()
 * @method ShiftPlan[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<Field>
 */
class ShiftPlanRepository extends AbstractRepository
{

}