<?php

namespace FOQ\ElasticaBundle\Doctrine\ORM;

use Doctrine\ORM\QueryBuilder;
use FOQ\ElasticaBundle\Doctrine\AbstractProvider;
use FOQ\ElasticaBundle\Exception\InvalidArgumentTypeException;

class Provider extends AbstractProvider
{
    /**
     * @see FOQ\ElasticaBundle\Doctrine\AbstractProvider::countObjects()
     */
    protected function countObjects($queryBuilder)
    {
        if (!$queryBuilder instanceof QueryBuilder) {
            throw new InvalidArgumentTypeException($queryBuilder, 'Doctrine\ORM\QueryBuilder');
        }

        /* Clone the query builder before altering its field selection and DQL,
         * lest we leave the query builder in a bad state for fetchSlice().
         */
        $qb = clone $queryBuilder;

        return $qb
            ->select($qb->expr()->count($queryBuilder->getRootAlias()))
            // Remove ordering for efficiency; it doesn't affect the count
            ->resetDQLPart('orderBy')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @see FOQ\ElasticaBundle\Doctrine\AbstractProvider::fetchSlice()
     */
    protected function fetchSlice($queryBuilder, $limit, $offset)
    {
        if (!$queryBuilder instanceof QueryBuilder) {
            throw new InvalidArgumentTypeException($queryBuilder, 'Doctrine\ORM\QueryBuilder');
        }

        return $queryBuilder
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * @see FOQ\ElasticaBundle\Doctrine\AbstractProvider::createQueryBuilder()
     */
    protected function createQueryBuilder()
    {
        return $this->managerRegistry
            ->getManagerForClass($this->objectClass)
            ->getRepository($this->objectClass)
            // ORM query builders require an alias argument
            ->{$this->options['query_builder_method']}('a');
    }
}
