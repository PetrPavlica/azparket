<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\UserInWorkplace;
use Doctrine\Common\Collections\Criteria;

/**
 * @method UserInWorkplace|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method UserInWorkplace|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method UserInWorkplace[] findAll()
 * @method UserInWorkplace[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<UserInWorkplace>
 */
class UserInWorkplaceRepository extends AbstractRepository
{

}