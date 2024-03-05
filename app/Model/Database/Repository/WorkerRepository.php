<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\Worker;
use Doctrine\Common\Collections\Criteria;

/**
 * @method Worker|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method Worker|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method Worker[] findAll()
 * @method Worker[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<Worker>
 */
class WorkerRepository extends AbstractRepository
{

}