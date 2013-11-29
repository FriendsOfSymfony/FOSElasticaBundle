<?php

namespace FOS\ElasticaBundle\Persister;

use Elastica\Document;
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
    public function __construct(Type $type, ModelToElasticaTransformerInterface $transformer, $objectClass, $serializer)
    {
        $this->type        = $type;
        $this->transformer = $transformer;
        $this->objectClass = $objectClass;
        $this->serializer  = $serializer;
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
        $this->type->addDocument($document);
    }

    /**
     * Replaces one object in the type
     *
     * @param object $object
     * @return null
     */
    public function replaceOne($object)
    {
        $document = $this->transformToElasticaDocument($object);
        $this->type->deleteById($document->getId());
        $this->type->addDocument($document);
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
        $docs = array();
        foreach ($objects as $object) {
            $docs[] = $this->transformToElasticaDocument($object);
        }
        $this->type->addDocuments($docs);
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
        $document = $this->transformer->transform($object, array());

        $data = call_user_func($this->serializer, $object);
        $document->setData($data);

        return $document;
    }
}
