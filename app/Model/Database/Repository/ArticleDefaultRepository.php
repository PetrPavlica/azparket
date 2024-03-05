<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\ArticleDefault;
use Doctrine\Common\Collections\Criteria;

/**
 * @method ArticleDefault|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method ArticleDefault|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method ArticleDefault[] findAll()
 * @method ArticleDefault[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<>
 */
class ArticleDefaultRepository extends AbstractRepository
{

}