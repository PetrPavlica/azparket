<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\ApproveState;
use Doctrine\Common\Collections\Criteria;

/**
 * @method ApproveState|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method ApproveState|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method ApproveState[] findAll()
 * @method ApproveState[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<Worker>
 */
class ApproveStateRepository extends AbstractRepository
{

}