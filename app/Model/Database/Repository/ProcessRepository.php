<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\Process;

/**
 * @method Process|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method Process|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method Process[] findAll()
 * @method Process[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<Process>
 */
class ProcessRepository extends AbstractRepository
{

}