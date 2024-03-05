<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\Employment;

/**
 * @method Employment|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method Employment|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method Employment[] findAll()
 * @method Employment[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<Employment>
 */
class EmploymentRepository extends AbstractRepository
{

}