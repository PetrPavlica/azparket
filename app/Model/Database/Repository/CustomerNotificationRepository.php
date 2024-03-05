<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\CustomerNotification;

/**
 * @method CustomerNotification|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method CustomerNotification|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method CustomerNotification[] findAll()
 * @method CustomerNotification[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<CustomerNotification>
 */
class CustomerNotificationRepository extends AbstractRepository
{

}