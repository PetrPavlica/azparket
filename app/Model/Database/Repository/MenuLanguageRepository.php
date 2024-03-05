<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\MenuLanguage;
use Doctrine\Common\Collections\Criteria;

/**
 * @method MenuLanguage|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method MenuLanguage|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method MenuLanguage[] findAll()
 * @method MenuLanguage[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<>
 */
class MenuLanguageRepository extends AbstractRepository
{

}