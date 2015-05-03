<?php

namespace FOS\ElasticaBundle\Doctrine\ORM;

use FOS\ElasticaBundle\Doctrine\AbstractElasticaToModelTransformer;
use Doctrine\ORM\Query;

/**
 * Maps Elastica documents with Doctrine objects
 * This mapper assumes an exact match between
 * elastica documents ids and doctrine object ids.
 */
class ElasticaToModelTransformer extends AbstractElasticaToModelTransformer
{
    const ENTITY_ALIAS = 'o';

    /**
     * Fetch objects for theses identifier values.
     *
     * @param array $identifierValues ids values
     * @param array $options transform options
     *
     * @return array of objects or arrays
     */
    protected function findByIdentifiers(array $identifierValues, array $options = array())
    {
        if (empty($identifierValues)) {
            return array();
        }
        $hydrationMode = isset($options['hydrate']) && $options['hydrate'] ? Query::HYDRATE_OBJECT : Query::HYDRATE_ARRAY;

        $qb = $this->getEntityQueryBuilder($options);
        $qb->andWhere($qb->expr()->in(static::ENTITY_ALIAS.'.'.$options['identifier'], ':values'))
            ->setParameter('values', $identifierValues);

        return $qb->getQuery()->setHydrationMode($hydrationMode)->execute();
    }

    /**
     * Retrieves a query builder to be used for querying by identifiers.
     *
     * @param array $options transform options
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getEntityQueryBuilder(array $options = array())
    {
        $options = array_merge($this->options, $options);

        $repository = $this->registry
            ->getManagerForClass($this->objectClass)
            ->getRepository($this->objectClass);

        return $repository->{$options['query_builder_method']}(static::ENTITY_ALIAS);
    }
}
