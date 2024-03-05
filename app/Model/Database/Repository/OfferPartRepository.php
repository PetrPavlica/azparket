<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\OfferPart;
use Doctrine\Common\Collections\Criteria;

/**
 * @method OfferPartRepository|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method OfferPartRepository|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method OfferPartRepository[] findAll()
 * @method OfferPartRepository[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<OfferPartRepository>
 */
class OfferPartRepository extends AbstractRepository
{

}