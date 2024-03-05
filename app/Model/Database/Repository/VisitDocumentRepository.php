<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\VisitDocument;
use Doctrine\Common\Collections\Criteria;

/**
 * @method VisitDocument|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method VisitDocument|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method VisitDocument[] findAll()
 * @method VisitDocument[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<VisitDocument>
 */
class VisitDocumentRepository extends AbstractRepository
{

}