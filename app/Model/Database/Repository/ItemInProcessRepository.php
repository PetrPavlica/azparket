<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\ItemInProcess;

/**
 * @method ItemInProcess|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method ItemInProcess|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method ItemInProcess[] findAll()
 * @method ItemInProcess[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<ItemInProcess>
 */
class ItemInProcessRepository extends AbstractRepository
{

}