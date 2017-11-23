<?php

namespace FOS\ElasticaBundle\Doctrine\PHPCR;

use FOS\ElasticaBundle\Doctrine\AbstractProvider;
use FOS\ElasticaBundle\Exception\InvalidArgumentTypeException;
use Doctrine\ODM\PHPCR\Query\Builder\QueryBuilder;
use Doctrine\ODM\PHPCR\Query\Query;

@trigger_error(sprintf('The %s class is deprecated since version 4.1 and will be removed in 5.0.', Provider::class), E_USER_DEPRECATED);

/**
 * @deprecated since 4.1 will be removed in 5.x. Use PagerProvider instead
 */
class Provider extends AbstractProvider
{
    const ENTITY_ALIAS = 'a';

    /**
     * Disables logging and returns the logger that was previously set.
     *
     * @return mixed
     */
    protected function disableLogging()
    {
        return;
    }

    /**
     * Reenables the logger with the previously returned logger from disableLogging();
     *
     * @param mixed $logger
     * @return mixed
     */
    protected function enableLogging($logger)
    {
        return;
    }

    /**
     * {@inheritDoc}
     */
    protected function countObjects($queryBuilder)
    {
        if (!$queryBuilder instanceof QueryBuilder) {
            throw new InvalidArgumentTypeException($queryBuilder, 'Doctrine\ODM\PHPCR\Query\Builder\QueryBuilder');
        }

        return $queryBuilder
            ->getQuery()
            ->execute(null, Query::HYDRATE_PHPCR)
            ->getRows()
            ->count();
    }

    /**
     * {@inheritDoc}
     */
    protected function fetchSlice($queryBuilder, $limit, $offset)
    {
        if (!$queryBuilder instanceof QueryBuilder) {
            throw new InvalidArgumentTypeException($queryBuilder, 'Doctrine\ODM\PHPCR\Query\Builder\QueryBuilder');
        }

        return $queryBuilder
            ->getQuery()
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getResult()
            ->toArray();
    }

    /**
     * {@inheritDoc}
     */
    protected function createQueryBuilder($method,array $arguments=array())
    {
        return $this->managerRegistry
            ->getManager()
            ->getRepository($this->objectClass)
            ->{$method}(static::ENTITY_ALIAS);
    }
}
