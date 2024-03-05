<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\TaskLog;

/**
 * @method TaskLog|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method TaskLog|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method TaskLog[] findAll()
 * @method TaskLog[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<TaskLog>
 */
class TaskLogRepository extends AbstractRepository
{

}