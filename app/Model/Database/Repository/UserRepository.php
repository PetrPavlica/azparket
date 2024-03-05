<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\User;
use App\Model\Database\Utils\SQLHelper;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;

/**
 * @method User|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method User|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method User[] findAll()
 * @method User[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<User>
 */
class UserRepository extends AbstractRepository
{   
    
    /** @var SQLHelper */
    private $SQLHelper;

    public function __construct(EntityManagerInterface $em, ClassMetadata $class)
    {
        parent::__construct($em, $class);
        $this->SQLHelper = new SQLHelper();
    }

    public function findOneByEmail(string $email): ?User
    {
        return $this->findOneBy(['email' => $email]);
    }

    /**
     * Get data for autocomplete with users
     * @param string $term search items
     * @return array results
     */
    public function getDataAutocompleteUsers($term, $where = null)
    {
        $columns = ['name', 'email', 'username'];
        $alias = 'u';

        $qb = $this->_em->getRepository(User::class)
            ->createQueryBuilder($alias)
            ->setMaxResults('20');
        if ($where) {
            $qb->where($alias . '.' . $where);
        }

        $qb = $this->SQLHelper->termToLikeQB($qb, $term, $alias, $columns);
        $result = $qb->getQuery()->getResult();

        $arr = [];
        if ($result) {
            foreach ($result as $item) {
                $arr[$item->id] = $item->name . ', (' . $item->email . ')';
            }
        }
        return $arr;
    }

}