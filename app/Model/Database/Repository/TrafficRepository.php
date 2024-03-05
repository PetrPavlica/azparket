<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\Traffic;
use App\Model\Database\Utils\SQLHelper;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;

/**
 * @method Traffic|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method Traffic|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method Traffic[] findAll()
 * @method Traffic[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<Traffic>
 */
class TrafficRepository extends AbstractRepository
{
    /** @var SQLHelper */
    private $SQLHelper;

    public function __construct(EntityManagerInterface $em, ClassMetadata $class)
    {
        parent::__construct($em, $class);
        $this->SQLHelper = new SQLHelper();
    }

    public function changeCaregivers($cOrig, $cNew) {
        $query = $this->getEntityManager()->getConnection()->prepare("
            UPDATE worker_on_traffic SET worker_id = '".$cNew."' WHERE worker_id = '".$cOrig."'
		");
        $query->executeStatement();
        $query = $this->getEntityManager()->getConnection()->prepare("
            UPDATE worker_on_traffic_substitute SET worker_id = '".$cNew."' WHERE worker_id = '".$cOrig."'
		");
        $query->executeStatement();
    }

    /**
     * @param $term
     * @return array
     */
    public function getDataAutocompleteTraffics($term)
    {
        $columns = ['name', 'street', 'city'];
        $alias = 'p';
        $like = $this->SQLHelper->termToLike($term, $alias, $columns);

        $result = $this->_em->getRepository(Traffic::class)
            ->createQueryBuilder($alias)
            ->where($like)
            ->andWhere($alias . '.active = 1')
            ->setMaxResults('20')
            ->getQuery()
            ->getResult();
        $arr = [];
        foreach ($result as $item) {
            $worker = [];
            foreach ($item->worker as $w){
                if (!$w->worker->active) {
                    continue;
                }
                $worker[] = $w->worker->id;
            }
            $workerSubstitute = [];
            foreach ($item->workerSubstitute as $w){
                if (!$w->worker->active) {
                    continue;
                }
                $workerSubstitute[] = $w->worker->id;
            }

            $arr[ $item->id ] = [
                $item->name,
                [
                    'ships' => $item->isCostProgram  ?  "{$item->costProgram} Kč" : "{$item->costDistance} km",
                    'customerOrdered' => $item->customerOrdered ? $item->customerOrdered->id : null,
                    'textcustomerOrdered' => $item->customerOrdered ? $item->customerOrdered->name : '',
                    'customer' => $item->customer ? $item->customer->id : null,
                    'textcustomer' => $item->customer ? $item->customer->name : '',
                    'worker' => $worker,
                    'workerSubstitute' => $workerSubstitute,
                ]
            ];
        }
        return $arr;
    }
}