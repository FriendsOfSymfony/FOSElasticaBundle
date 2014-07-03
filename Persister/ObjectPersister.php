<?php

namespace FOS\ElasticaBundle\Persister;

use Psr\Log\LoggerInterface;
use Elastica\Exception\BulkException;
use Elastica\Exception\NotFoundException;
use FOS\ElasticaBundle\Transformer\ModelToElasticaTransformerInterface;
use Elastica\Type;
use Elastica\Document;

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
    protected $logger;

    public function __construct(Type $type, ModelToElasticaTransformerInterface $transformer, $objectClass, array $fields)
    {
        $this->type            = $type;
        $this->transformer     = $transformer;
        $this->objectClass     = $objectClass;
        $this->fields          = $fields;
    }

    /**
     * If the ObjectPersister handles a given object.
     *
     * @param object $object
     * @return bool
     */
    public function handlesObject($object)
    {
        return $object instanceof $this->objectClass;
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Log exception if logger defined for persister belonging to the current listener, otherwise re-throw
     *
     * @param BulkException $e
     * @throws BulkException
     * @return null
     */
    private function log(BulkException $e)
    {
        if (! $this->logger) {
            throw $e;
        }

        $this->logger->error($e);
    }

    /**
     * Insert one object into the type
     * The object will be transformed to an elastica document
     *
     * @param object $object
     */
    public function insertOne($object)
    {
        $this->insertMany(array($object));
    }

    /**
     * Replaces one object in the type
     *
     * @param object $object
     * @return null
     **/
    public function replaceOne($object)
    {
        $this->replaceMany(array($object));
    }

    /**
     * Deletes one object in the type
     *
     * @param object $object
     * @return null
     **/
    public function deleteOne($object)
    {
        $this->deleteMany(array($object));
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
        $this->deleteManyByIdentifiers(array($id));
    }

    /**
     * Bulk insert an array of objects in the type for the given method
     *
     * @param array $objects array of domain model objects
     * @param string Method to call
     */
    public function insertMany(array $objects)
    {
        $documents = array();
        foreach ($objects as $object) {
            $documents[] = $this->transformToElasticaDocument($object);
        }
        try {
            $this->type->addDocuments($documents);
        } catch (BulkException $e) {
            $this->log($e);
        }
    }

    /**
     * Bulk update an array of objects in the type.  Create document if it does not already exist.
     *
     * @param array $objects array of domain model objects
     */
    public function replaceMany(array $objects)
    {
        $documents = array();
        foreach ($objects as $object) {
            $document = $this->transformToElasticaDocument($object);
            $document->setDocAsUpsert(true);
            $documents[] = $document;
        }

        try {
            $this->type->updateDocuments($documents);
        } catch (BulkException $e) {
            $this->log($e);
        }
    }

    /**
     * Bulk deletes an array of objects in the type
     *
     * @param array $objects array of domain model objects
     */
    public function deleteMany(array $objects)
    {
        $documents = array();
        foreach ($objects as $object) {
            $documents[] = $this->transformToElasticaDocument($object);
        }
        try {
            $this->type->deleteDocuments($documents);
        } catch (BulkException $e) {
            $this->log($e);
        }
    }

    /**
     * Bulk deletes records from an array of identifiers
     *
     * @param array $identifiers array of domain model object identifiers
     */
    public function deleteManyByIdentifiers(array $identifiers)
    {
        try {
            $this->type->getIndex()->getClient()->deleteIds($identifiers, $this->type->getIndex(), $this->type);
        } catch (BulkException $e) {
            $this->log($e);
        }
    }

    /**
     * Transforms an object to an elastica document
     *
     * @param object $object
     * @return Document the elastica document
     */
    public function transformToElasticaDocument($object)
    {
        return $this->transformer->transform($object, $this->fields);
    }
}
