<?php

namespace FOS\ElasticaBundle\Transformer;

use Elastica\Type;

/**
 * Maps Elastica documents with model objects.
 */
interface ModelToElasticaTransformerInterface
{
    /**
     * Transforms an object into an elastica object having the required keys.
     *
     * @param object    $object the object to convert
     * @param array     $fields the keys we want to have in the returned array
     * @param Type|null $type   the current type
     *
     * @return \Elastica\Document
     **/
    public function transform($object, array $fields, Type $type = null);
}
