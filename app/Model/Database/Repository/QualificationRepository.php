<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\Qualification;

/**
 * @method Qualification|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method Qualification|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method Qualification[] findAll()
 * @method Qualification[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<Qualification>
 */
class QualificationRepository extends AbstractRepository
{

}