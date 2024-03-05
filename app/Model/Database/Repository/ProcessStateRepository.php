<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\ProcessState;

/**
 * @method ProcessState|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method ProcessState|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method ProcessState[] findAll()
 * @method ProcessState[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<ProcessState>
 */
class ProcessStateRepository extends AbstractRepository
{

}