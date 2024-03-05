<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\ProductionPlan;

/**
 * @method ProductionPlan|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method ProductionPlan|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method ProductionPlan[] findAll()
 * @method ProductionPlan[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<Field>
 */
class ProductionPlanRepository extends AbstractRepository
{

}