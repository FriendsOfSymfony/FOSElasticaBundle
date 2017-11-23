<?php

namespace FOS\ElasticaBundle\Doctrine\MongoDB;

use Doctrine\ODM\MongoDB\Query\Builder;
use FOS\ElasticaBundle\Doctrine\AbstractProvider;
use FOS\ElasticaBundle\Exception\InvalidArgumentTypeException;

@trigger_error(sprintf('The %s class is deprecated since version 4.1 and will be removed in 5.0.', Provider::class), E_USER_DEPRECATED);

/**
 * @deprecated since 4.1 will be removed in 5.x. Use PagerProvider instead 
 */
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
    protected function createQueryBuilder($method, array $arguments = array())
    {
        $repository = $this->managerRegistry
            ->getManagerForClass($this->objectClass)
            ->getRepository($this->objectClass);

        return call_user_func_array([$repository, $method], $arguments);
    }
}
