<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\MaterialNeedBuy;
use Doctrine\Common\Collections\Criteria;

/**
 * @method MaterialNeedBuy|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method MaterialNeedBuy|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method MaterialNeedBuy[] findAll()
 * @method MaterialNeedBuy[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<MaterialNeedBuy>
 */
class MaterialNeedBuyRepository extends AbstractRepository
{

}