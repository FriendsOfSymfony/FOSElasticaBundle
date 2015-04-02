<?php

namespace FOS\ElasticaBundle\Transformer;

/**
 * Maps Elastica documents with model objects.
 */
interface ElasticaToModelTransformerInterface
{
    /**
     * Transforms an array of elastica objects into an array of
     * model objects fetched from the doctrine repository.
     *
     * @param array $elasticaObjects array of elastica objects
     * @param array $options         transform options
     *
     * @return array of model objects
     **/
    public function transform(array $elasticaObjects, array $options = array());

    public function hybridTransform(array $elasticaObjects, array $options = array());

    /**
     * Returns the object class used by the transformer.
     *
     * @return string
     */
    public function getObjectClass();

    /**
     * Returns the identifier field from the options.
     *
     * @return string the identifier field
     */
    public function getIdentifierField();
}
