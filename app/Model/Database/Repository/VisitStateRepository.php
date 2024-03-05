<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\VisitState;
use Doctrine\Common\Collections\Criteria;

/**
 * @method VisitState|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method VisitState|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method VisitState[] findAll()
 * @method VisitState[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<VisitState>
 */
class VisitStateRepository extends AbstractRepository
{

}