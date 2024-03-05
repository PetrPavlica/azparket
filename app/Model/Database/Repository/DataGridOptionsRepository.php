<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\DataGridOptions;

/**
 * @method DataGridOptions|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method DataGridOptions|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method DataGridOptions[] findAll()
 * @method DataGridOptions[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<DataGridOptions>
 */
class DataGridOptionsRepository extends AbstractRepository
{

}