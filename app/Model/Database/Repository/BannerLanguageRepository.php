<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\BannerLanguage;
use Doctrine\Common\Collections\Criteria;

/**
 * @method BannerLanguage|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method BannerLanguage|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method BannerLanguage[] findAll()
 * @method BannerLanguage[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<>
 */
class BannerLanguageRepository extends AbstractRepository
{

}