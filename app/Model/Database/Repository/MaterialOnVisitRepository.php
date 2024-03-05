<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\MaterialOnVisit;
use Doctrine\Common\Collections\Criteria;

/**
 * @method MaterialOnVisit|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method MaterialOnVisit|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method MaterialOnVisit[] findAll()
 * @method MaterialOnVisit[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<MaterialOnVisit>
 */
class MaterialOnVisitRepository extends AbstractRepository
{

}