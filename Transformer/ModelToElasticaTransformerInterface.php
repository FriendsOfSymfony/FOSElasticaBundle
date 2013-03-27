<?php

namespace FOS\ElasticaBundle\Transformer;

/**
 * Maps Elastica documents with model objects
 */
interface ModelToElasticaTransformerInterface
{
    /**
     * Transforms an object into an elastica object having the required keys
     *
     * @param object $object the object to convert
     * @param array $fields the keys we want to have in the returned array
     * @return Elastica_Document
     **/
    function transform($object, array $fields);
}
