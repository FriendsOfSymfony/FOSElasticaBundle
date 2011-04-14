<?php

namespace FOQ\ElasticaBundle;

/**
 * Maps Elastica documents with persisted objects
 */
interface MapperInterface
{
    /**
     * Transforms an array of elastica objects into an array of
     * model objects fetched from the doctrine repository
     *
     * @return array
     **/
    function fromElasticaObjects(array $elasticaObjects);
}
