<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\CustomerOrdered;
use App\Model\Database\Utils\SQLHelper;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;

/**
 * @method CustomerOrdered|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method CustomerOrdered|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method CustomerOrdered[] findAll()
 * @method CustomerOrdered[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<CustomerOrdered>
 */
class CustomerOrderedRepository extends AbstractRepository
{
    /** @var SQLHelper */
    private $SQLHelper;

    public function __construct(EntityManagerInterface $em, ClassMetadata $class)
    {
        parent::__construct($em, $class);
        $this->SQLHelper = new SQLHelper();
    }

    public function findPairsForSelect(): ?array
    {
        $entities = $this->findBy(['active' => 1]);
        $res = array();
        foreach ($entities as $entity) {
            $res[$entity->id] = $entity->company . (($entity->name || $entity->surname) ? ' (' . $entity->name . ' ' . $entity->surname . ')' : '');
        }
        return $res;
    }

    /**
     * @param $term
     * @return array
     */
    public function getDataAutocompleteCustomerOrdered($term)
    {
        $columns = ['name', 'street', 'city'];
        $alias = 'p';
        $like = $this->SQLHelper->termToLike($term, $alias, $columns);

        $result = $this->_em->getRepository(CustomerOrdered::class)
            ->createQueryBuilder($alias)
            ->where($like)
            ->andWhere($alias . '.active = 1')
            ->setMaxResults('20')
            ->getQuery()
            ->getResult();
        $arr = [];
        foreach ($result as $item) {
            $arr[ $item->id ] = $item->name;
        }
        return $arr;
    }
}