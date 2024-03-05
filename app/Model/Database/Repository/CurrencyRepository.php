<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\Currency;

/**
 * @method Currency|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method Currency|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method Currency[] findAll()
 * @method Currency[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<Currency>
 */
class CurrencyRepository extends AbstractRepository
{

}