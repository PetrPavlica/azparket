<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\ItemType;

/**
 * @method ItemType|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method ItemType|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method ItemType[] findAll()
 * @method ItemType[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<ItemType>
 */
class ItemTypeRepository extends AbstractRepository
{

}