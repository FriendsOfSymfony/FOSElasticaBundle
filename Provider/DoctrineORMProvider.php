<?php

namespace FOQ\ElasticaBundle\Provider;

class DoctrineORMProvider extends AbstractDoctrineProvider
{
    /**
     * Counts the objects of a query builder
     *
     * @param queryBuilder
     * @return int
     **/
    protected function countObjects($queryBuilder)
    {
        return $queryBuilder->count()->getQuery()->execute();
    }

    /**
     * Fetches a slice of objects
     *
     * @param queryBuilder
     * @param int limit
     * @param int offset
     * @return array of objects
     **/
    protected function fetchSlice($queryBuilder, $limit, $offset)
    {
        $queryBuilder->setFirstResult($offset);
        $queryBuilder->setMaxResults($limit);

        return $queryBuilder->getQuery()->getArrayResult();
    }
}
