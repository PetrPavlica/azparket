<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\Setting;

/**
 * @method Setting|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method Setting|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method Setting[] findAll()
 * @method Setting[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<Setting>
 */
class SettingRepository extends AbstractRepository
{

}