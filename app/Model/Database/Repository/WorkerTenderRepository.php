<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\WorkerTender;

/**
 * @method WorkerTender|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method WorkerTender|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method WorkerTender[] findAll()
 * @method WorkerTender[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<WorkerTender>
 */
class WorkerTenderRepository extends AbstractRepository
{

}