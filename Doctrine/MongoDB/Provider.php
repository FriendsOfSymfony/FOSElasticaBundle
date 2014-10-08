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
     * Reenables the logger with the previously returned logger from disableLogging();
     *
     * @param mixed $logger
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
     * @see FOS\ElasticaBundle\Doctrine\AbstractProvider::countObjects()
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
     * @see FOS\ElasticaBundle\Doctrine\AbstractProvider::fetchSlice()
     */
    protected function fetchSlice($queryBuilder, $limit, $offset, array $previousSlice)
    {
        if (!$queryBuilder instanceof Builder) {
            throw new InvalidArgumentTypeException($queryBuilder, 'Doctrine\ODM\MongoDB\Query\Builder');
        }

        $lastObject = array_pop($previousSlice);

        if ($lastObject) {
            $queryBuilder
                ->field('_id')->gt($lastObject->getId())
                ->skip(0);
        } else {
            $queryBuilder->skip($offset);
        }

        return $queryBuilder
            ->limit($limit)
            ->sort(array('_id' => 'asc'))
            ->getQuery()
            ->execute()
            ->toArray();
    }

    /**
     * @see FOS\ElasticaBundle\Doctrine\AbstractProvider::createQueryBuilder()
     */
    protected function createQueryBuilder()
    {
        return $this->managerRegistry
            ->getManagerForClass($this->objectClass)
            ->getRepository($this->objectClass)
            ->{$this->options['query_builder_method']}();
    }
}
