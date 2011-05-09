<?php

namespace FOQ\ElasticaBundle\Provider;

class DoctrineORMProvider extends AbstractDoctrineProvider
{
    /**
     * Counts the objects of a query builder
     *
     * OMG this implementation is radical. Yes. There seems to be
     * no easy way to do that with Doctrine ORM 2.0.
     * Please tell me if you have a better idea.
     *
     * @param queryBuilder
     * @return int
     **/
    protected function countObjects($queryBuilder)
    {
        return PHP_INT_MAX;
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
