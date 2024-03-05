<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\ConfiguratorNode;

/**
 * @method ConfiguratorNode|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method ConfiguratorNode|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method ConfiguratorNode[] findAll()
 * @method ConfiguratorNode[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<ConfiguratorNode>
 */
class ConfiguratorNodeRepository extends AbstractRepository
{

}