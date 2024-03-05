<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\Task;

/**
 * @method Task|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method Task|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method Task[] findAll()
 * @method Task[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<Task>
 */
class TaskRepository extends AbstractRepository
{

}