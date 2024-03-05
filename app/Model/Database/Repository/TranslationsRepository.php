<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\Translations;
use Doctrine\Common\Collections\Criteria;

/**
 * @method Translations|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method Translations|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method Translations[] findAll()
 * @method Translations[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<>
 */
class TranslationsRepository extends AbstractRepository
{

}