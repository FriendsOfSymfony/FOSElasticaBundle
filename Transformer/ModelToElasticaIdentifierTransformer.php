<?php

namespace FOS\ElasticaBundle\Transformer;

use Elastica\Document;

/**
 * Creates an elastica document with the id of
 * the doctrine object as elastica document id
 */
class ModelToElasticaIdentifierTransformer extends ModelToElasticaAutoTransformer
{
    /**
     * Creates an elastica document with the id of the doctrine object as id
     *
     * @param object $object the object to convert
     * @param array  $fields the keys we want to have in the returned array
     *
     * @return Document
     **/
    public function transform($object, array $fields)
    {
        $identifier = $this->propertyAccessor->getValue($object, $this->options['identifier']);
        return new Document($identifier);
    }
}
