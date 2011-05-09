<?php

namespace FOQ\ElasticaBundle\Transformer;

use Elastica_Document;

/**
 * Maps Elastica documents with Doctrine objects
 * This mapper assumes an exact match between
 * elastica documents ids and doctrine object ids
 */
class ElasticaToModelDoctrineORMTransformer extends ElasticaToModelAbstractDoctrineTransformer
{
    /**
     * Fetch objects for theses identifier values
     *
     * @param string $class the model class
     * @param string $identifierField like 'id'
     * @param array $identifierValues ids values
     * @param mixed $hydrate whether or not to hydrate the objects, false returns arrays
     * @return array of objects or arrays
     */
    protected function findByIdentifiers($class, $identifierField, array $identifierValues, $hydrate)
    {
        throw new \Exception('Not implemented yet. Implement me! See ElasticaToModelDoctrineMongoDBTransformer as an example.');
    }
}
