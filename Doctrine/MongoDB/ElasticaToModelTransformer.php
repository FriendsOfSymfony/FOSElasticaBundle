<?php

namespace FOS\ElasticaBundle\Doctrine\MongoDB;

use FOS\ElasticaBundle\Doctrine\AbstractElasticaToModelTransformer;
use Doctrine\ORM\Query;

/**
 * Maps Elastica documents with Doctrine objects
 * This mapper assumes an exact match between
 * elastica documents ids and doctrine object ids.
 */
class ElasticaToModelTransformer extends AbstractElasticaToModelTransformer
{
    /**
     * Fetch objects for theses identifier values.
     *
     * @param array   $identifierValues ids values
     * @param array   $options
     *
     * @return array of objects or arrays
     */
    protected function findByIdentifiers(array $identifierValues, array $options = array())
    {
        $options = array_merge($this->options, $options);

        return $this->registry
            ->getManagerForClass($this->objectClass)
            ->getRepository($this->objectClass)
            ->{$this->options['query_builder_method']}($this->objectClass)
            ->field($this->options['identifier'])->in($identifierValues)
            ->hydrate($options['hydrate'])
            ->getQuery()
            ->execute()
            ->toArray();
    }
}
