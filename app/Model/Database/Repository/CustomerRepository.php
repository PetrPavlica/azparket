<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\Customer;
use App\Model\Database\Utils\SQLHelper;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;

/**
 * @method Customer|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method Customer|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method Customer[] findAll()
 * @method Customer[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<Customer>
 */
class CustomerRepository extends AbstractRepository
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
    public function getDataAutocompleteCustomers($term)
    {
        $columns = ['name', 'surname', 'street', 'city'];
        $alias = 'p';
        $like = $this->SQLHelper->termToLike($term, $alias, $columns);

        $result = $this->_em->getRepository(Customer::class)
            ->createQueryBuilder($alias)
            ->where($like)
            ->andWhere($alias . '.active = 1')
            ->setMaxResults('20')
            ->getQuery()
            ->getResult();
        $arr = [];
        foreach ($result as $item) {
            $arr[ $item->id ] = $item->name . ' ' . $item->surname . ' (' . $item->email . ', ' . $item->phone . ')';
        }
        return $arr;
    }

    /**
     * Get specific customer for autocomplete
     * @param $customer
     * @return string customer
     */
    public function getSpecificCustomer($item)
    {
        if (is_numeric($item)) {
            $item = $this->find($item);
        }
        if (!$item) {
            return NULL;
        }

        $res = "";
        //if ($item->company && $item->company != '')
        //    $res = $item->company . ", ";
        $res .= $item->name . ' ' . $item->surname . ' (' . $item->email . ', ' . $item->phone . ')';

        return $res;
    }
}