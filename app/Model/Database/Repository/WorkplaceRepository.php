<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\Workplace;
use Doctrine\Common\Collections\Criteria;

/**
 * @method Workplace|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method Workplace|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method Workplace[] findAll()
 * @method Workplace[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<Workplace>
 */
class WorkplaceRepository extends AbstractRepository
{

}