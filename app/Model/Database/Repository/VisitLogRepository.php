<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\VisitLog;
use Doctrine\Common\Collections\Criteria;

/**
 * @method VisitLog|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method VisitLog|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method VisitLog[] findAll()
 * @method VisitLog[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<VisitLog>
 */
class VisitLogRepository extends AbstractRepository
{

}