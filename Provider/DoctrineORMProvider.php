<?php

namespace FOQ\ElasticaBundle\Provider;

class DoctrineORMProvider extends AbstractDoctrineProvider
{
    /**
     * Counts the objects of a query builder
     *
     * @return int
     **/
    protected function countObjects($queryBuilder)
    {
        return $queryBuilder->count()->getQuery()->execute();
    }
}
