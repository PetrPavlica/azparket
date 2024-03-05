<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\Skill;

/**
 * @method Skill|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method Skill|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method Skill[] findAll()
 * @method Skill[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<Skill>
 */
class SkillRepository extends AbstractRepository
{

}