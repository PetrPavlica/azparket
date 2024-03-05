<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\Field;

/**
 * @method Field|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method Field|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method Field[] findAll()
 * @method Field[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<Field>
 */
class FieldRepository extends AbstractRepository
{

}