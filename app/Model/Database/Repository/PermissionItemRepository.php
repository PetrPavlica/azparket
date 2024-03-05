<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\PermissionItem;

/**
 * @method PermissionItem|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method PermissionItem|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method PermissionItem[] findAll()
 * @method PermissionItem[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<PermissionItem>
 */
class PermissionItemRepository extends AbstractRepository
{

}