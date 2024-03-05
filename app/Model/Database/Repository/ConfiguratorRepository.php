<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\Configurator;

/**
 * @method Configurator|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method Configurator|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method Configurator[] findAll()
 * @method Configurator[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<Configurator>
 */
class ConfiguratorRepository extends AbstractRepository
{

}