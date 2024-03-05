<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\ManagedRisc;

/**
 * @method ManagedRisc|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method ManagedRisc|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method ManagedRisc[] findAll()
 * @method ManagedRisc[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<Field>
 */
class ManagedRiscRepository extends AbstractRepository
{

}