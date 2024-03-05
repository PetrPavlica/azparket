<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\Absence;
use Doctrine\Common\Collections\Criteria;

/**
 * @method Absence|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method Absence|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method Absence[] findAll()
 * @method Absence[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<Absence>
 */
class AbsenceRepository extends AbstractRepository
{

}