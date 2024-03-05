<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\ProductLanguage;
use Doctrine\Common\Collections\Criteria;

/**
 * @method ProductLanguage|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method ProductLanguage|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method ProductLanguage[] findAll()
 * @method ProductLanguage[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<Field>
 */
class ProductLanguageRepository extends AbstractRepository
{

}