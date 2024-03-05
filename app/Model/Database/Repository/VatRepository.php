<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\Vat;
use Doctrine\Common\Collections\Criteria;

/**
 * @method Vat|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method Vat|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method Vat[] findAll()
 * @method Vat[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<Vat>
 */
class VatRepository extends AbstractRepository
{
    
}