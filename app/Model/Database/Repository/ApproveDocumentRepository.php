<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\ApproveDocument;
use Doctrine\Common\Collections\Criteria;

/**
 * @method ApproveDocument|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method ApproveDocument|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method ApproveDocument[] findAll()
 * @method ApproveDocument[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<Worker>
 */
class ApproveDocumentRepository extends AbstractRepository
{

}