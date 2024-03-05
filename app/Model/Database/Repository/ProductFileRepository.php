<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\ProductFile;
use Doctrine\Common\Collections\Criteria;

/**
 * @method ProductFile|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method ProductFile|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method ProductFile[] findAll()
 * @method ProductFile[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<Field>
 */
class ProductFileRepository extends AbstractRepository
{

}