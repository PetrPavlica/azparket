<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\TaskComment;
use Doctrine\Common\Collections\Criteria;

/**
 * @method TaskComment|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method TaskComment|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method TaskComment[] findAll()
 * @method TaskComment[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<TaskComment>
 */
class TaskCommentRepository extends AbstractRepository
{

}