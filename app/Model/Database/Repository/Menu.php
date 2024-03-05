<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\Menu;
use Doctrine\Common\Collections\Criteria;

/**
 * @method Menu|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method Menu|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method Menu[] findAll()
 * @method Menu[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<>
 */
class MenuRepository extends AbstractRepository
{

}