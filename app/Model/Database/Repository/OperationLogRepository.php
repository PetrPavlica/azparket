<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\OperationLog;
use Doctrine\Common\Collections\Criteria;

/**
 * @method OperationLog|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method OperationLog|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method OperationLog[] findAll()
 * @method OperationLog[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<Worker>
 */
class OperationLogRepository extends AbstractRepository
{

}