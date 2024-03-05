<?php

namespace App\Model\Database\Utils;

use Doctrine\ORM\QueryBuilder;

class SQLHelper
{
    /**
     * Helper method for convert searching term to SQL query for Like form
     * @param string $term
     * @param string $prefix
     * @param array $columns
     * @return string
     */
    public function termToLike($term, $alias, $columns) {
        $term = explode(" ", $term);
        $term2 = "";
        foreach ($term as $item) {
            $item = trim($item);
            if ($item != "") {
                $term2 .= " (";
                if ($alias) {
                    foreach ($columns as $col) {
                        $term2 .= " " . $alias . "." . $col . " LIKE '%" . addslashes($item) . "%' OR";
                    } 
                } else {
                    foreach ($columns as $col) {
                        $term2 .= " " . $col . " LIKE '%" . addslashes($item) . "%' OR";
                    } 
                }
                $term2 = substr($term2, 0, -2);
                $term2 .= ") AND";
            }
        }
        $term2 = substr($term2, 0, -3);
        return $term2;
    }

    /**
     * Helper method for convert searching term to SQL query for Like form
     * @param QueryBuilder $qb
     * @param string $term
     * @param string $prefix
     * @param array $columns
     * @return QueryBuilder
     */
    public function termToLikeQB($qb, $term, $alias, $columns) {
        $term = explode(" ", $term);
        $parId = 1;
        foreach ($term as $item) {
            $item = trim($item);
            if ($item != "") {
                $term2 = [];
                $parameters = [];
                if ($alias) {
                    foreach ($columns as $col) {
                        $term2[] = $alias . "." . $col . " LIKE :par".$parId;
                        $parameters['par'.$parId] = '%'.$item.'%';
                        $parId++;
                    }
                } else {
                    foreach ($columns as $col) {
                        $term2[] = $col . " LIKE :par".$parId;
                        $parameters['par'.$parId] = '%'.$item.'%';
                        $parId++;
                    }
                }
                $qb->andWhere(implode(' OR ', $term2));
                if ($parameters) {
                    foreach ($parameters as $k => $par) {
                        $qb->setParameter($k, $par);
                    }
                }
            }
        }


        return $qb;
    }
}