<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\VacationType;

/**
 * @method VacationType|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method VacationType|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method VacationType[] findAll()
 * @method VacationType[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<Field>
 */
class VacationTypeRepository extends AbstractRepository
{

}