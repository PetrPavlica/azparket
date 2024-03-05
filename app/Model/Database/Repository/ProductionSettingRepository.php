<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\ProductionSetting;

/**
 * @method ProductionSetting|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method ProductionSetting|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method ProductionSetting[] findAll()
 * @method ProductionSetting[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<Field>
 */
class ProductionSettingRepository extends AbstractRepository
{

}