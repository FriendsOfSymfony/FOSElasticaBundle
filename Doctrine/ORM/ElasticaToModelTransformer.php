<?php

namespace FOQ\ElasticaBundle\Doctrine\ORM;

use FOQ\ElasticaBundle\Doctrine\AbstractElasticaToModelTransformer;
use Elastica_Document;
use Doctrine\ORM\Query;

/**
 * Maps Elastica documents with Doctrine objects
 * This mapper assumes an exact match between
 * elastica documents ids and doctrine object ids
 */
class ElasticaToModelTransformer extends AbstractElasticaToModelTransformer
{
    /**
     * Fetch objects for theses identifier values
     *
     * @param array $identifierValues ids values
     * @param Boolean $hydrate whether or not to hydrate the objects, false returns arrays
     * @return array of objects or arrays
     */
    protected function findByIdentifiers(array $identifierValues, $hydrate)
    {
        if (empty($identifierValues)) {
            return array();
        }
        $hydrationMode = $hydrate ? Query::HYDRATE_OBJECT : Query::HYDRATE_ARRAY;
        $qb = $this->registry
            ->getManagerForClass($this->objectClass)
            ->getRepository($this->objectClass)
            // ORM query builders require an alias argument, so just use $objectClass
            ->{$this->options['query_builder_method']}($this->objectClass);

        /* @var $qb \Doctrine\ORM\QueryBuilder */
        $qb->where($qb->expr()->in($this->objectClass.'.'.$this->options['identifier'], ':values'))
            ->setParameter('values', $identifierValues);

        return $qb->getQuery()->setHydrationMode($hydrationMode)->execute();
    }
}
