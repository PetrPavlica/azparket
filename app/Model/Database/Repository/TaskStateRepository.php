<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\TaskState;

/**
 * @method TaskState|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method TaskState|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method TaskState[] findAll()
 * @method TaskState[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<TaskState>
 */
class TaskStateRepository extends AbstractRepository
{

}