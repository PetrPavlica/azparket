<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\Article;
use Doctrine\Common\Collections\Criteria;

/**
 * @method Article|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method Article|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method Article[] findAll()
 * @method Article[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<>
 */
class ArticleRepository extends AbstractRepository
{

}