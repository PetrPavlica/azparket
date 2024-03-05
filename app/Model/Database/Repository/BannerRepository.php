<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\Banner;
use Doctrine\Common\Collections\Criteria;

/**
 * @method Banner|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method Banner|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method Banner[] findAll()
 * @method Banner[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<>
 */
class BannerRepository extends AbstractRepository
{

}