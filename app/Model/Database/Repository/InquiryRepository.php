<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\Inquiry;
use App\Model\Database\Utils\SQLHelper;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;

/**
 * @method Inquiry|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method Inquiry|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method Inquiry[] findAll()
 * @method Inquiry[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<Inquiry>
 */
class InquiryRepository extends AbstractRepository
{ 
    
    /** @var SQLHelper */
    private $SQLHelper;

    public function __construct(EntityManagerInterface $em, ClassMetadata $class)
    {
        parent::__construct($em, $class);
        $this->SQLHelper = new SQLHelper();
    }

    /**
     * Get data for autocomplete with inquiry
     * @param string $term search items
     * @return array results
     */
    public function getDataAutocompleteInquiries($term, $where = null)
    {
        $columns = ['i.id', 'i.installCity', 'i.installZip', 'cust.name', 'cust.surname', 'cust.company'];

        $qb = $this->_em->getRepository(Inquiry::class)->createQueryBuilder('i')
            ->leftJoin('i.customer', 'cust')
            ->setMaxResults('20');
        if ($where) {
            $qb->where('i.' . $where);
        }

        $qb = $this->SQLHelper->termToLikeQB($qb, $term, '', $columns);
        $result = $qb->getQuery()->getResult();

        $arr = [];
        if ($result) {
            foreach ($result as $item) {
                $arr[$item->id] = $item->id . ' - ' .
                    ($item->configurator ? $item->configurator->name . ': ': '') .
                    $item->installCity . ', ' . $item->installZip;
            }
        }
        return $arr;
    }
}