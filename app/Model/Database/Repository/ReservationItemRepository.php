<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\ReservationItem;
use Doctrine\Common\Collections\Criteria;

/**
 * @method ReservationItem|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method ReservationItem|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method ReservationItem[] findAll()
 * @method ReservationItem[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<>
 */
class ReservationItemRepository extends AbstractRepository
{

}