<?php

namespace FOS\ElasticaBundle\Persister;

use FOS\ElasticaBundle\Transformer\ModelToElasticaTransformerInterface;
use Elastica_Type;
use Elastica_Document;

/**
 * Inserts, replaces and deletes single documents in an elastica type
 * Accepts domain model objects and converts them to elastica documents
 *
 * @author Thibault Duplessis <thibault.duplessis@gmail.com>
 */
class ObjectPersister implements ObjectPersisterInterface
{
    protected $type;
    protected $transformer;
    protected $objectClass;
    protected $fields;

    public function __construct(Elastica_Type $type, ModelToElasticaTransformerInterface $transformer, $objectClass, array $fields)
    {
        $this->type            = $type;
        $this->transformer     = $transformer;
        $this->objectClass     = $objectClass;
        $this->fields          = $fields;
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
     **/
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
        $documents = array();
        foreach ($objects as $object) {
            $documents[] = $this->transformToElasticaDocument($object);
        }
        $this->type->addDocuments($documents);
    }

    /**
     * Transforms an object to an elastica document
     *
     * @param object $object
     * @return Elastica_Document the elastica document
     */
    public function transformToElasticaDocument($object)
    {
        return $this->transformer->transform($object, $this->fields);
    }
}
