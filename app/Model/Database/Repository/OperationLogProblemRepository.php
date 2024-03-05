<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\OperationLogProblem;
use Doctrine\Common\Collections\Criteria;

/**
 * @method OperationLogProblem|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method OperationLogProblem|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method OperationLogProblem[] findAll()
 * @method OperationLogProblem[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<Worker>
 */
class OperationLogProblemRepository extends AbstractRepository
{

}