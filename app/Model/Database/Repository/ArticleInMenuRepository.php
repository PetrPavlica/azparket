<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\ArticleInMenu;
use Doctrine\Common\Collections\Criteria;

/**
 * @method ArticleInMenu|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method ArticleInMenu|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method ArticleInMenu[] findAll()
 * @method ArticleInMenu[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<>
 */
class ArticleInMenuRepository extends AbstractRepository
{

}