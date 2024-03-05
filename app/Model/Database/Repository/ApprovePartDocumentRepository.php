<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\ApprovePartDocument;
use Doctrine\Common\Collections\Criteria;

/**
 * @method ApprovePartDocument|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method ApprovePartDocument|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method ApprovePartDocument[] findAll()
 * @method ApprovePartDocument[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<Worker>
 */
class ApprovePartDocumentRepository extends AbstractRepository
{

}