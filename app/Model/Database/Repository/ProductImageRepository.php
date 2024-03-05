<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\ProductImage;
use Doctrine\Common\Collections\Criteria;

/**
 * @method ProductImage|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method ProductImage|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method ProductImage[] findAll()
 * @method ProductImage[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<Field>
 */
class ProductImageRepository extends AbstractRepository
{

}