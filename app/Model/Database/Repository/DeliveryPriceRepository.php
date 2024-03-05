<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\DeliveryPrice;

/**
 * @method DeliveryPrice|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method DeliveryPrice|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method DeliveryPrice[] findAll()
 * @method DeliveryPrice[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<DeliveryPrice>
 */
class DeliveryPriceRepository extends AbstractRepository
{

}