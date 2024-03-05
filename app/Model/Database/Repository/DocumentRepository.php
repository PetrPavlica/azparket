<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\Document;

/**
 * @method Document|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method Document|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method Document[] findAll()
 * @method Document[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<Document>
 */
class DocumentRepository extends AbstractRepository
{

}