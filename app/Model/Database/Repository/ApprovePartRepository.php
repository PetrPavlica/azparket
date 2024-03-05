<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\ApprovePart;
use Doctrine\Common\Collections\Criteria;

/**
 * @method ApprovePart|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method ApprovePart|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method ApprovePart[] findAll()
 * @method ApprovePart[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<Worker>
 */
class ApprovePartRepository extends AbstractRepository
{

}