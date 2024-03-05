<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\WorkerInUser;

/**
 * @method WorkerInUser|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method WorkerInUser|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method WorkerInUser[] findAll()
 * @method WorkerInUser[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<WorkerInUser>
 */
class WorkerInUserRepository extends AbstractRepository
{

}