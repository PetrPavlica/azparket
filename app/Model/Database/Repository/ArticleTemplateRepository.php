<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\ArticleTemplate;
use Doctrine\Common\Collections\Criteria;

/**
 * @method ArticleTemplate|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method ArticleTemplate|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method ArticleTemplate[] findAll()
 * @method ArticleTemplate[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<>
 */
class ArticleTemplateRepository extends AbstractRepository
{

}