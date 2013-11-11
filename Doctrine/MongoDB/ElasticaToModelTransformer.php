<?php

namespace FOS\ElasticaBundle\Doctrine\MongoDB;

use FOS\ElasticaBundle\Doctrine\AbstractElasticaToModelTransformer;

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
        return $this->registry
            ->getManagerForClass($this->objectClass)
            ->{$this->options['query_builder_method']}($this->objectClass)
            ->field($this->options['identifier'])->in($identifierValues)
            ->hydrate($hydrate)
            ->getQuery()
            ->execute()
            ->toArray();
    }
}
