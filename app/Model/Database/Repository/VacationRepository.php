<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\Vacation;

/**
 * @method Vacation|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method Vacation|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method Vacation[] findAll()
 * @method Vacation[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<Field>
 */
class VacationRepository extends AbstractRepository
{

}