<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\PermissionRule;

/**
 * @method PermissionRule|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method PermissionRule|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method PermissionRule[] findAll()
 * @method PermissionRule[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<PermissionRule>
 */
class PermissionRuleRepository extends AbstractRepository
{

}