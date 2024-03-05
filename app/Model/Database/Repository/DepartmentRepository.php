<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\Department;

/**
 * @method Department|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method Department|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method Department[] findAll()
 * @method Department[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<Department>
 */
class DepartmentRepository extends AbstractRepository
{

}