<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\Offer;
use Doctrine\Common\Collections\Criteria;

/**
 * @method Offer|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method Offer|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method Offer[] findAll()
 * @method Offer[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<Offer>
 */
class OfferRepository extends AbstractRepository
{

}