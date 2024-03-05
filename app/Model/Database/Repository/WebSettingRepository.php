<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\WebSetting;
use Doctrine\Common\Collections\Criteria;

/**
 * @method WebSetting|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method WebSetting|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method WebSetting[] findAll()
 * @method WebSetting[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<>
 */
class WebSettingRepository extends AbstractRepository
{

}