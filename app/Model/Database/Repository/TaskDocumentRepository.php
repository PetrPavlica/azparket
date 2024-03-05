<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\TaskDocument;
use Doctrine\Common\Collections\Criteria;

/**
 * @method TaskDocument|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method TaskDocument|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method TaskDocument[] findAll()
 * @method TaskDocument[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<TaskDocument>
 */
class TaskDocumentRepository extends AbstractRepository
{

}