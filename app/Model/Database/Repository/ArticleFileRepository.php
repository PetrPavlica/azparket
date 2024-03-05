<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\ArticleFile;
use Doctrine\Common\Collections\Criteria;

/**
 * @method ArticleFile|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method ArticleFile|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method ArticleFile[] findAll()
 * @method ArticleFile[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<>
 */
class ArticleFileRepository extends AbstractRepository
{

}