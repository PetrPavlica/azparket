<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\ApproveTime;
use Doctrine\Common\Collections\Criteria;

/**
 * @method ApproveTime|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method ApproveTime|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method ApproveTime[] findAll()
 * @method ApproveTime[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<Worker>
 */
class ApproveTimeRepository extends AbstractRepository
{

}