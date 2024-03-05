<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\MaterialStock;
use Doctrine\Common\Collections\Criteria;

/**
 * @method MaterialStock|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method MaterialStock|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method MaterialStock[] findAll()
 * @method MaterialStock[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<MaterialStock>
 */
class MaterialStockRepository extends AbstractRepository
{

}