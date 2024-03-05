<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\ProductInMenu;
use Doctrine\Common\Collections\Criteria;

/**
 * @method ProductInMenu|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method ProductInMenu|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method ProductInMenu[] findAll()
 * @method ProductInMenu[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<Field>
 */
class ProductInMenuRepository extends AbstractRepository
{

}