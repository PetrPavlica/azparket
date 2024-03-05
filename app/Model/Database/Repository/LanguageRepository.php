<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\Language;
use Doctrine\Common\Collections\Criteria;

/**
 * @method Language|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method Language|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method Language[] findAll()
 * @method Language[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<>
 */
class LanguageRepository extends AbstractRepository
{

}