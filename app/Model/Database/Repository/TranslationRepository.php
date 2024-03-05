<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\Translation;
use Doctrine\Common\Collections\Criteria;

/**
 * @method Translation|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method Translation|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method Translation[] findAll()
 * @method Translation[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<>
 */
class TranslationRepository extends AbstractRepository
{

}