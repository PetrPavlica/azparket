<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\OfferProduct;
use Doctrine\Common\Collections\Criteria;

/**
 * @method OfferProduct|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method OfferProduct|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method OfferProduct[] findAll()
 * @method OfferProduct[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<OfferProduct>
 */
class OfferProductRepository extends AbstractRepository
{

}