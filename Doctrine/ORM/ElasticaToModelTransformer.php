<?php

namespace FOS\ElasticaBundle\Doctrine\ORM;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use FOS\ElasticaBundle\Doctrine\AbstractElasticaToModelTransformer;

/**
 * Maps Elastica documents with Doctrine objects
 * This mapper assumes an exact match between
 * elastica documents ids and doctrine object ids
 */
class ElasticaToModelTransformer extends AbstractElasticaToModelTransformer
{
    const ENTITY_ALIAS = 'o';

    /**
     * {@inheritdoc}
     */
    protected function findByIdentifiers(array $identifierValues, $hydrate)
    {
        if (empty($identifierValues)) {
            return array();
        }

        $qb = $this->getEntityQueryBuilder();
        $qb->where($qb->expr()->in(static::ENTITY_ALIAS.'.'.$this->options['identifier'], ':values'))
            ->setParameter('values', $identifierValues);

        return $qb->getQuery()->setHydrationMode($hydrate ? Query::HYDRATE_OBJECT : Query::HYDRATE_ARRAY)->execute();
    }

    /**
     * Retrieves a query builder to be used for querying by identifiers
     *
     * @return QueryBuilder
     */
    protected function getEntityQueryBuilder()
    {
        $repository = $this->registry
            ->getManagerForClass($this->objectClass)
            ->getRepository($this->objectClass);

        return $repository->{$this->options['query_builder_method']}(static::ENTITY_ALIAS);
    }
}
