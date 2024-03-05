<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\CustomerState;

/**
 * @method CustomerState|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method CustomerState|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method CustomerState[] findAll()
 * @method CustomerState[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<CustomerState>
 */
class CustomerStateRepository extends AbstractRepository
{

}