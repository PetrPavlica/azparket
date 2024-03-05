<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\WorkerNote;

/**
 * @method WorkerNote|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method WorkerNote|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method WorkerNote[] findAll()
 * @method WorkerNote[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<WorkerNote>
 */
class WorkerNoteRepository extends AbstractRepository
{

}