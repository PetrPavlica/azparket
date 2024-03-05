<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\Machine;

/**
 * @method Machine|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method Machine|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method Machine[] findAll()
 * @method Machine[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<Field>
 */
class MachineRepository extends AbstractRepository
{

}