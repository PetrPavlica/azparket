<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\Visit;
use Doctrine\Common\Collections\Criteria;

/**
 * @method Visit|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method Visit|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method Visit[] findAll()
 * @method Visit[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<Visit>
 */
class VisitRepository extends AbstractRepository
{

}