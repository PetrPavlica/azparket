<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\AbsenceReason;
use Doctrine\Common\Collections\Criteria;

/**
 * @method AbsenceReason|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method AbsenceReason|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method AbsenceReason[] findAll()
 * @method AbsenceReason[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<AbsenceReason>
 */
class AbsenceReasonRepository extends AbstractRepository
{

}