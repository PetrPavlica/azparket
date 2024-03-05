<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\ApproveNorm;
use Doctrine\Common\Collections\Criteria;

/**
 * @method ApproveNorm|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method ApproveNorm|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method ApproveNorm[] findAll()
 * @method ApproveNorm[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<Worker>
 */
class ApproveNormRepository extends AbstractRepository
{

}