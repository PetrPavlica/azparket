<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\PermissionGroup;

/**
 * @method PermissionGroup|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method PermissionGroup|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method PermissionGroup[] findAll()
 * @method PermissionGroup[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<PermissionGroup>
 */
class PermissionGroupRepository extends AbstractRepository
{

}