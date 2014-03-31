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
     * @param object[] $elasticaObjects array of elastica objects
     *
     * @return object[]|\ArrayObject array of model objects
     */
    public function transform(array $elasticaObjects);

    /**
     * @param array $elasticaObjects
     *
     * @return mixed
     */
    public function hybridTransform(array $elasticaObjects);

    /**
     * Returns the object class used by the transformer.
     *
     * @return string
     */
    public function getObjectClass();

    /**
     * Returns the identifier field from the options
     *
     * @return string the identifier field
     */
    public function getIdentifierField();
}
