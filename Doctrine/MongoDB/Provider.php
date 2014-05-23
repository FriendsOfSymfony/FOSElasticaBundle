<?php

namespace FOS\ElasticaBundle\Doctrine\MongoDB;

use Doctrine\ODM\MongoDB\Query\Builder;
use FOS\ElasticaBundle\Doctrine\AbstractProvider;
use FOS\ElasticaBundle\Exception\InvalidArgumentTypeException;

class Provider extends AbstractProvider
{
    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    protected function fetchSlice($queryBuilder, $limit, $offset)
    {
        if (!$queryBuilder instanceof Builder) {
            throw new InvalidArgumentTypeException($queryBuilder, 'Doctrine\ODM\MongoDB\Query\Builder');
        }

        return $queryBuilder
            ->limit($limit)
            ->skip($offset)
            ->getQuery()
            ->execute()
            ->toArray();
    }

    /**
     * {@inheritdoc}
     */
    protected function createQueryBuilder()
    {
        return $this->manager->getRepository($this->objectClass)
            ->{$this->options['query_builder_method']}();
    }
}
