<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\OperationLogItem;
use Doctrine\Common\Collections\Criteria;

/**
 * @method OperationLogItem|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method OperationLogItem|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method OperationLogItem[] findAll()
 * @method OperationLogItem[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<Worker>
 */
class OperationLogItemRepository extends AbstractRepository
{

}