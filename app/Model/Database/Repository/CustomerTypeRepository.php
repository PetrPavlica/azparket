<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\CustomerType;

/**
 * @method CustomerType|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method CustomerType|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method CustomerType[] findAll()
 * @method CustomerType[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<CustomerType>
 */
class CustomerTypeRepository extends AbstractRepository
{

}