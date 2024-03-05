<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\MaterialGroup;
use Doctrine\Common\Collections\Criteria;

/**
 * @method MaterialGroup|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method MaterialGroup|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method MaterialGroup[] findAll()
 * @method MaterialGroup[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<MaterialGroup>
 */
class MaterialGroupRepository extends AbstractRepository
{

}