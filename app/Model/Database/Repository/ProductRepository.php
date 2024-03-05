<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\Product;
use App\Model\Database\Utils\SQLHelper;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;

/**
 * @method Product|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method Product|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method Product[] findAll()
 * @method Product[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<Field>
 */
class ProductRepository extends AbstractRepository
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
    public function getDataAutocompleteProducts($term)
    {
        $columns = ['p.klic_polozky', 'p.nazev_polozky', 'p.alter_nazev', 'pl.name'];
        $like = $this->SQLHelper->termToLike($term, '', $columns);

        $result = $this->_em->getRepository(Product::class)
            ->createQueryBuilder('p')
            ->leftJoin('\App\Model\Database\Entity\ProductLanguage', 'pl', 'WITH', 'p.id = pl.product')
            ->leftJoin('pl.lang', 'l')
            ->where($like)
            ->andWhere("p.active = 1 AND (pl.id IS NULL OR l.code = 'cz')")
            ->groupBy('p.id')
            ->setMaxResults('20')
            ->getQuery()
            ->getResult();

        $arr = [];
        foreach ($result as $item) {
            $arr[ $item->id ] = [
                ($item->klic_polozky ? $item->klic_polozky . ': ' : '') . $item->nazev_polozky,
                $item->id,
                $item->klic_polozky,
                $item->evid_cena_pol
            ];
        }
        return $arr;
    }
}