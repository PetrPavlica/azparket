<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\Approve;
use Doctrine\Common\Collections\Criteria;

/**
 * @method Approve|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method Approve|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method Approve[] findAll()
 * @method Approve[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<Worker>
 */
class ApproveRepository extends AbstractRepository
{

}