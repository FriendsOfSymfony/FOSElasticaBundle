<?php

namespace FOS\ElasticaBundle\Persister;

use Elastica\Type;
use FOS\ElasticaBundle\Transformer\ModelToElasticaTransformerInterface;

/**
 * Inserts, replaces and deletes single objects in an elastica type, making use
 * of elastica's serializer support to convert objects in to elastica documents.
 * Accepts domain model objects and passes them directly to elastica
 *
 * @author Lea Haensenberber <lea.haensenberger@gmail.com>
 */
class ObjectSerializerPersister extends ObjectPersister
{
    public function __construct(Type $type, ModelToElasticaTransformerInterface $transformer, $objectClass)
    {
        $this->type            = $type;
        $this->transformer     = $transformer;
        $this->objectClass     = $objectClass;
    }

    /**
     * Insert one object into the type
     * The object will be transformed to an elastica document
     *
     * @param object $object
     */
    public function insertOne($object)
    {
        $document = $this->transformToElasticaDocument($object);
        $this->type->addObject($object, $document);
    }

    /**
     * Replaces one object in the type
     *
     * @param object $object
     * @return null
     **/
    public function replaceOne($object)
    {
        $document = $this->transformToElasticaDocument($object);
        $this->type->deleteById($document->getId());
        $this->type->addObject($object, $document);
    }

    /**
     * Deletes one object in the type
     *
     * @param object $object
     * @return null
     **/
    public function deleteOne($object)
    {
        $document = $this->transformToElasticaDocument($object);
        $this->type->deleteById($document->getId());
    }

    /**
     * Deletes one object in the type by id
     *
     * @param mixed $id
     *
     * @return null
     **/
    public function deleteById($id)
    {
        $this->type->deleteById($id);
    }


    /**
     * Inserts an array of objects in the type
     *
     * @param array $objects array of domain model objects
     **/
    public function insertMany(array $objects)
    {
        foreach ($objects as $object) {
            $this->insertOne($object);
        }
    }

    /**
     * Transforms an object to an elastica document
     * with just the identifier set
     *
     * @param object $object
     * @return Document the elastica document
     */
    public function transformToElasticaDocument($object)
    {
        return $this->transformer->transform($object, array());
    }
}
