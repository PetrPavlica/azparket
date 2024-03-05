<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\ApiClient;

/**
 * @method ApiClient|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method ApiClient|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method ApiClient[] findAll()
 * @method ApiClient[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<ApiClient>
 */
class ApiClientRepository extends AbstractRepository
{

}