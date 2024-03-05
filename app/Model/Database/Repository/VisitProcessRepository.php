<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\VisitProcess;
use App\Model\Database\Utils\SQLHelper;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;

/**
 * @method VisitProcess|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method VisitProcess|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method VisitProcess[] findAll()
 * @method VisitProcess[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<VisitProcess>
 */
class VisitProcessRepository extends AbstractRepository
{
    /** @var SQLHelper */
    private $SQLHelper;

    public function __construct(EntityManagerInterface $em, ClassMetadata $class)
    {
        parent::__construct($em, $class);
        $this->SQLHelper = new SQLHelper();
    }

    /**
     * @param $term
     * @return array
     */
    public function getDataAutocompleteVisitProcess($term)
    {
        $columns = ['name', 'orderId'];
        $alias = 'vp';
        $like = $this->SQLHelper->termToLike($term, $alias, $columns);

        $result = $this->_em->getRepository(VisitProcess::class)
            ->createQueryBuilder($alias)
            ->where($like)
            ->setMaxResults('20')
            ->getQuery()
            ->getResult();
        $arr = [];
        foreach ($result as $item) {
            $arr[ $item->id ] = [
                $item->name,
                [
                    'orderId' => $item->orderId ? $item->orderId : null,
                ]
            ];
        }
        return $arr;
    }
}