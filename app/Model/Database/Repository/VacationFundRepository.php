<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\VacationFund;

/**
 * @method VacationFund|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method VacationFund|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method VacationFund[] findAll()
 * @method VacationFund[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<Field>
 */
class VacationFundRepository extends AbstractRepository
{

}