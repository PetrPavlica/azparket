<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\ArticleEvent;
use Doctrine\Common\Collections\Criteria;

/**
 * @method ArticleEvent|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method ArticleEvent|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method ArticleEvent[] findAll()
 * @method ArticleEvent[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<>
 */
class ArticleEventRepository extends AbstractRepository
{

}