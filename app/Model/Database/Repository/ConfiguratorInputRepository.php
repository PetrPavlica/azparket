<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\ConfiguratorInput;

/**
 * @method ConfiguratorInput|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method ConfiguratorInput|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method ConfiguratorInput[] findAll()
 * @method ConfiguratorInput[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<ConfiguratorInput>
 */
class ConfiguratorInputRepository extends AbstractRepository
{

}