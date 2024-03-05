<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\SkillType;

/**
 * @method SkillType|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method SkillType|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method SkillType[] findAll()
 * @method SkillType[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<SkillType>
 */
class SkillTypeRepository extends AbstractRepository
{

}