<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\ReservationPlan;

/**
 * @method ReservationPlan|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method ReservationPlan|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method ReservationPlan[] findAll()
 * @method ReservationPlan[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<Field>
 */
class ReservationPlanRepository extends AbstractRepository
{

}