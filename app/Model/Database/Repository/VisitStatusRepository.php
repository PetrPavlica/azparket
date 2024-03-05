<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\VisitStatus;
use Doctrine\Common\Collections\Criteria;

/**
 * @method VisitStatus|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method VisitStatus|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method VisitStatus[] findAll()
 * @method VisitStatus[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<VisitStatus>
 */
class VisitStatusRepository extends AbstractRepository
{

}