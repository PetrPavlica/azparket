<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\ArticleFileInLanguage;
use Doctrine\Common\Collections\Criteria;

/**
 * @method ArticleFileInLanguage|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method ArticleFileInLanguage|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method ArticleFileInLanguage[] findAll()
 * @method ArticleFileInLanguage[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<>
 */
class ArticleFileInLanguageRepository extends AbstractRepository
{

}