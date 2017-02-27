<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Doctrine\PHPCR;

use Doctrine\ODM\PHPCR\Query\Builder\QueryBuilder;
use Doctrine\ODM\PHPCR\Query\Query;
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
        return;
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
        return;
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    protected function createQueryBuilder($method, array $arguments = array())
    {
        return $this->managerRegistry
            ->getManager()
            ->getRepository($this->objectClass)
            ->{$method}(static::ENTITY_ALIAS);
    }
}
