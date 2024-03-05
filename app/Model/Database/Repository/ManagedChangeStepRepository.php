<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\ManagedChangeStep;

/**
 * @method ManagedChangeStep|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method ManagedChangeStep|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method ManagedChangeStep[] findAll()
 * @method ManagedChangeStep[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<Field>
 */
class ManagedChangeStepRepository extends AbstractRepository
{

}