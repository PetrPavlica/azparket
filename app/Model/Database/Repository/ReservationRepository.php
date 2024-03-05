<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\Reservation;
use Doctrine\Common\Collections\Criteria;

/**
 * @method Reservation|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method Reservation|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method Reservation[] findAll()
 * @method Reservation[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<>
 */
class ReservationRepository extends AbstractRepository
{

}