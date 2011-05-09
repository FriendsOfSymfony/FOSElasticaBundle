<?php

namespace FOQ\ElasticaBundle\Provider;

class DoctrineMongoDBProvider extends AbstractDoctrineProvider
{
    /**
     * Counts the objects of a query builder
     *
     * @return int
     **/
    protected function countObjects($queryBuilder)
    {
        return $queryBuilder->getQuery()->count();
    }
}
