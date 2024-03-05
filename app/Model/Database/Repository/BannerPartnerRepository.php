<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\BannerPartner;
use Doctrine\Common\Collections\Criteria;

/**
 * @method BannerPartner|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method BannerPartner|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method BannerPartner[] findAll()
 * @method BannerPartner[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<>
 */
class BannerPartnerRepository extends AbstractRepository
{

}