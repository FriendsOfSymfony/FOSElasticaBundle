<?php

namespace FOS\ElasticaBundle\Doctrine\ORM;

use Doctrine\ORM\QueryBuilder;
use FOS\ElasticaBundle\Doctrine\AbstractProvider;
use FOS\ElasticaBundle\Exception\InvalidArgumentTypeException;

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
        $configuration = $this->managerRegistry
            ->getManagerForClass($this->objectClass)
            ->getConnection()
            ->getConfiguration();

        $logger = $configuration->getSQLLogger();
        $configuration->setSQLLogger(null);

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

        $configuration->setSQLLogger($logger);
    }

    /**
     * {@inheritDoc}
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
        $rootAliases = $queryBuilder->getRootAliases();

        return $qb
            ->select($qb->expr()->count($rootAliases[0]))
            // Remove ordering for efficiency; it doesn't affect the count
            ->resetDQLPart('orderBy')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * This method should remain in sync with SliceFetcher::fetch until it is deprecated and removed.
     *
     * {@inheritDoc}
     */
    protected function fetchSlice($queryBuilder, $limit, $offset)
    {
        if (!$queryBuilder instanceof QueryBuilder) {
            throw new InvalidArgumentTypeException($queryBuilder, 'Doctrine\ORM\QueryBuilder');
        }

        /*
         * An orderBy DQL  part is required to avoid fetching the same row twice.
         * @see http://stackoverflow.com/questions/6314879/does-limit-offset-length-require-order-by-for-pagination
         * @see http://www.postgresql.org/docs/current/static/queries-limit.html
         * @see http://www.sqlite.org/lang_select.html#orderby
         */
        $orderBy = $queryBuilder->getDQLPart('orderBy');
        if (empty($orderBy)) {
            $rootAliases = $queryBuilder->getRootAliases();
            $identifierFieldNames = $this->managerRegistry
                ->getManagerForClass($this->objectClass)
                ->getClassMetadata($this->objectClass)
                ->getIdentifierFieldNames();
            foreach ($identifierFieldNames as $fieldName) {
                $queryBuilder->addOrderBy($rootAliases[0].'.'.$fieldName);
            }
        }

        return $queryBuilder
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * {@inheritDoc}
     */
    protected function createQueryBuilder($method, array $arguments = array())
    {
        $repository = $this->managerRegistry
            ->getManagerForClass($this->objectClass)
            ->getRepository($this->objectClass);
        // ORM query builders require an alias argument
        $arguments = [static::ENTITY_ALIAS] + $arguments;

        return call_user_func_array([$repository, $method], $arguments);
    }
}
