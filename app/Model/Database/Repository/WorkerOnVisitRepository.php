<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\WorkerOnVisit;
use Doctrine\Common\Collections\Criteria;

/**
 * @method WorkerOnVisit|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method WorkerOnVisit|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method WorkerOnVisit[] findAll()
 * @method WorkerOnVisit[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<WorkerOnVisit>
 */
class WorkerOnVisitRepository extends AbstractRepository
{

}