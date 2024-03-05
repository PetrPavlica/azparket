<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\ArticleNew;
use Doctrine\Common\Collections\Criteria;

/**
 * @method ArticleNew|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method ArticleNew|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method ArticleNew[] findAll()
 * @method ArticleNew[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<>
 */
class ArticleNewRepository extends AbstractRepository
{

}