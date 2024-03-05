<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\ManagedChange;

/**
 * @method ManagedChange|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method ManagedChange|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method ManagedChange[] findAll()
 * @method ManagedChange[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<Field>
 */
class ManagedChangeRepository extends AbstractRepository
{

}