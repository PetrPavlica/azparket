<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\ReservationProduct;

/**
 * @method ReservationProduct|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method ReservationProduct|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method ReservationProduct[] findAll()
 * @method ReservationProduct[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<Field>
 */
class ReservationProductRepository extends AbstractRepository
{

}