<?php

namespace FOS\ElasticaBundle\Transformer;

/**
 * Maps Elastica documents with model objects
 */
interface ElasticaToModelTransformerInterface
{
    /**
     * Transforms an array of elastica objects into an array of
     * model objects fetched from the doctrine repository
     *
     * @param array of elastica objects
     * @return array of model objects
     **/
    function transform(array $elasticaObjects);

    function hybridTransform(array $elasticaObjects);

    /**
     * Returns the object class used by the transformer.
     *
     * @return string
     */
    function getObjectClass();

    /**
     * Returns the identifier field from the options
     *
     * @return string the identifier field
     */
    function getIdentifierField();
}
