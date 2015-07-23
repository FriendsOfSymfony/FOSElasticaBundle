<?php

namespace FOS\ElasticaBundle\Doctrine\MongoDB;

use Doctrine\ODM\MongoDB\Query\Builder;
use FOS\ElasticaBundle\Doctrine\AbstractProvider;
use FOS\ElasticaBundle\Exception\InvalidArgumentTypeException;

class Provider extends AbstractProvider
{
    /**
     * Disables logging and returns the logger that was previously set.
     *
     * @return mixed
     */
    protected function disableLogging()
    {
        $configuration = $this->managerRegistry
            ->getManagerForClass($this->objectClass)
            ->getConnection()
            ->getConfiguration();

        $logger = $configuration->getLoggerCallable();
        $configuration->setLoggerCallable(null);

        return $logger;
    }

    /**
     * Reenables the logger with the previously returned logger from disableLogging();.
     *
     * @param mixed $logger
     *
     * @return mixed
     */
    protected function enableLogging($logger)
    {
        $configuration = $this->managerRegistry
            ->getManagerForClass($this->objectClass)
            ->getConnection()
            ->getConfiguration();

        $configuration->setLoggerCallable($logger);
    }

    /**
     * {@inheritDoc}
     */
    protected function countObjects($queryBuilder)
    {
        if (!$queryBuilder instanceof Builder) {
            throw new InvalidArgumentTypeException($queryBuilder, 'Doctrine\ODM\MongoDB\Query\Builder');
        }

        return $queryBuilder
            ->getQuery()
            ->count();
    }

    /**
     * {@inheritDoc}
     */
    protected function fetchSlice($queryBuilder, $limit, $offset)
    {
        if (!$queryBuilder instanceof Builder) {
            throw new InvalidArgumentTypeException($queryBuilder, 'Doctrine\ODM\MongoDB\Query\Builder');
        }

        return $queryBuilder
            ->skip($offset)
            ->limit($limit)
            ->getQuery()
            ->execute()
            ->toArray();
    }

    /**
     * {@inheritDoc}
     */
    protected function createQueryBuilder($method)
    {
        return $this->managerRegistry
            ->getManagerForClass($this->objectClass)
            ->getRepository($this->objectClass)
            ->{$method}();
    }
}
