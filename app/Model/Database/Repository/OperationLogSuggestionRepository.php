<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\OperationLogSuggestion;
use Doctrine\Common\Collections\Criteria;

/**
 * @method OperationLogSuggestion|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method OperationLogSuggestion|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method OperationLogSuggestion[] findAll()
 * @method OperationLogSuggestion[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<Worker>
 */
class OperationLogSuggestionRepository extends AbstractRepository
{

}