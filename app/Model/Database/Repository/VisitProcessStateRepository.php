<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\VisitProcessState;
use Doctrine\Common\Collections\Criteria;

/**
 * @method VisitProcessState|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method VisitProcessState|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method VisitProcessState[] findAll()
 * @method VisitProcessState[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<VisitProcessState>
 */
class VisitProcessStateRepository extends AbstractRepository
{

}