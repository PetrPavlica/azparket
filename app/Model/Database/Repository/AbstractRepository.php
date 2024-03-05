<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * @template TEntityClass
 * @extends EntityRepository<TEntityClass>
 */
abstract class AbstractRepository extends EntityRepository
{

    /**
     * Fetches all records like $key => $value pairs
     *
     * @param mixed[] $criteria
     * @param mixed[] $orderBy
     * @return mixed[]
     */
    public function findPairs(?string $key, string $value, array $criteria = [], array $orderBy = []): array
    {
        if ($key === null) {
            $key = $this->getClassMetadata()->getSingleIdentifierFieldName();
        }

        $qb = $this->createQueryBuilder('e')
            ->select(['e.' . $value, 'e.' . $key])
            ->resetDQLPart('from')
            ->from($this->getEntityName(), 'e', 'e.' . $key);

        foreach ($criteria as $k => $v) {
            if (is_array($v)) {
                $qb->andWhere(sprintf('e.%s IN(:%s)', $k, $k))->setParameter($k, array_values($v));
            } else {
                $qb->andWhere(sprintf('e.%s = :%s', $k, $k))->setParameter($k, $v);
            }
        }

        foreach ($orderBy as $column => $order) {
            $qb->addOrderBy(sprintf('e.%s', $column), $order);
        }

        return array_map(function ($row) {
            return reset($row);
        }, $qb->getQuery()->getArrayResult());
    }

    /**
     * Fetches all records like $key => $value pairs
     *
     * @param array $criteria
     * @param array $orderBy
     * @return mixed[]
     */
    public function findOneBySpecific(array $criteria, array $orderBy = NULL)
    {
        $qb = $this->createQueryBuilder('e')
            ->select('e')
            ->resetDQLPart('from')
            ->from($this->getEntityName(), 'e');

        $i = 1;
        foreach ($criteria as $k => $v) {
            if (is_array($v)) {
                $qb->andWhere(sprintf('e.%s IN(?%s)', $k, $i))->setParameter($i, array_values($v));
            } else {
                $qb->andWhere(sprintf('e.%s=?%s', $k, $i))->setParameter($i, $v);
            }
            $i++;
        }

        foreach ($orderBy as $column => $order) {
            $qb->addOrderBy(sprintf('e.%s', $column), $order);
        }

        $qb->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }
}