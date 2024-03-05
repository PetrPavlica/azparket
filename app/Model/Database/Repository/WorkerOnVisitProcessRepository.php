<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\WorkerOnVisitProcess;
use Doctrine\Common\Collections\Criteria;

/**
 * @method WorkerOnVisitProcess|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method WorkerOnVisitProcess|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method WorkerOnVisitProcess[] findAll()
 * @method WorkerOnVisitProcess[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<WorkerOnVisitProcess>
 */
class WorkerOnVisitProcessRepository extends AbstractRepository
{

}