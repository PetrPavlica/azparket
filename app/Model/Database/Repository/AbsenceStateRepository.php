<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\AbsenceState;
use Doctrine\Common\Collections\Criteria;

/**
 * @method AbsenceState|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method AbsenceState|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method AbsenceState[] findAll()
 * @method AbsenceState[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<AbsenceState>
 */
class AbsenceStateRepository extends AbstractRepository
{

}