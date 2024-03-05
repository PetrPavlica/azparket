<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\Material;
use App\Model\Database\Utils\SQLHelper;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;

/**
 * @method Material|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method Material|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method Material[] findAll()
 * @method Material[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<Material>
 */
class MaterialRepository extends AbstractRepository
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
    public function getDataAutocompleteMaterials($term, $refrigerant = false)
    {
        $columns = ['name', 'number', 'description'];
        $alias = 'm';
        $like = $this->SQLHelper->termToLike($term, $alias, $columns);

        $columnsGroup = ['name'];
        $aliasGroup = 'g';
        $likeGroup = $this->SQLHelper->termToLike($term, $aliasGroup, $columnsGroup);

        $result = $this->_em->getRepository(Material::class)
            ->createQueryBuilder($alias)
            ->leftJoin(\App\Model\Database\Entity\MaterialGroup::class, $aliasGroup, 'WITH', $alias.'.group = '.$aliasGroup.'.id')
            ->where($like . ' OR ' . $likeGroup)
            ->andWhere($alias . '.active = 1');
        if ($refrigerant) {
            $result = $result->andWhere($alias.'.group = 1');
        }
        $result = $result->setMaxResults('20')
            ->getQuery()
            ->getResult();
        $arr = [];
        foreach ($result as $item) {
            $arr[ $item->id ] = [
                $item->name,
                [
                    'number' => $item->number,
                    'name' => $item->name,
                    'unit' => $item->unit,
                    'stock' => $item->stock ? $item->stock->number /*. ' - ' . $item->stock->name*/ : null,
                ]
            ];
        }
        return $arr;
    }
}