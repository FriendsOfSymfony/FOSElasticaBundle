<?php

namespace FOQ\ElasticaBundle\Doctrine\ORM;

use FOQ\ElasticaBundle\Doctrine\AbstractProvider;

class Provider extends AbstractProvider
{
    /**
     * Counts the objects of a query builder
     *
     * @param queryBuilder
     * @return int
     **/
    protected function countObjects($queryBuilder)
    {
        $qb = clone $queryBuilder;
        $qb->select($qb->expr()->count($queryBuilder->getRootAlias()))
            ->resetDQLPart('orderBy'); // no need to order the query. It does not change the count and make the query less efficient.

        return $qb->getQuery()->getSingleScalarResult();
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

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * Creates the query builder used to fetch the documents to index
     *
     * @return query builder
     **/
    protected function createQueryBuilder()
    {
        return $this->registry
            ->getManagerForClass($this->objectClass)
            ->getRepository($this->objectClass)
            ->{$this->options['query_builder_method']}('a');
    }
}
