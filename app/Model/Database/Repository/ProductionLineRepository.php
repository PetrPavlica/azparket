<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\ProductionLine;

/**
 * @method ProductionLine|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method ProductionLine|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method ProductionLine[] findAll()
 * @method ProductionLine[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<ProductionLine>
 */
class ProductionLineRepository extends AbstractRepository
{

}