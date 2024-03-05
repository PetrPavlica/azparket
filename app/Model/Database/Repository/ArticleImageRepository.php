<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\ArticleImage;
use Doctrine\Common\Collections\Criteria;

/**
 * @method ArticleImage|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method ArticleImage|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method ArticleImage[] findAll()
 * @method ArticleImage[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<>
 */
class ArticleImageRepository extends AbstractRepository
{

}