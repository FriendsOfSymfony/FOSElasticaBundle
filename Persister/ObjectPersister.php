<?php

namespace FOQ\ElasticaBundle\Persister;

use FOQ\ElasticaBundle\Provider\ProviderInterface;
use FOQ\ElasticaBundle\Transformer\ModelToElasticaTransformerInterface;
use FOQ\ElasticaBundle\Registry\MappingRegistry;
use Symfony\Component\HttpKernel\Log\LoggerInterface;
use Elastica_Type;
use Elastica_Document;
use Exception;

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
    protected $mappingRegistry;
    protected $logger;
    protected $throwExceptions;

    public function __construct(Elastica_Type $type, ModelToElasticaTransformerInterface $transformer, $objectClass, MappingRegistry $mappingRegistry, LoggerInterface $logger = null, $throwExceptions = true)
    {
        $this->type            = $type;
        $this->transformer     = $transformer;
        $this->objectClass     = $objectClass;
        $this->mappingRegistry = $mappingRegistry;
        $this->logger          = $logger;
        $this->throwExceptions = true;
    }

    /**
     * Insert one object into the type
     * The object will be transformed to an elastica document
     *
     * @param object $object
     */
    public function insertOne($object)
    {
        try {
            $document = $this->transformToElasticaDocument($object);
            $this->type->addDocument($document);
        } catch (Exception $e) {
            $this->onError($e);
        }
    }

    /**
     * Replaces one object in the type
     *
     * @param object $object
     * @return null
     **/
    public function replaceOne($object)
    {
        try {
            $document = $this->transformToElasticaDocument($object);
            $this->type->deleteById($document->getId());
            $this->type->addDocument($document);
        } catch (Exception $e) {
            $this->onError($e);
        }
    }

    /**
     * Deletes one object in the type
     *
     * @param object $object
     * @return null
     **/
    public function deleteOne($object)
    {
        try {
            $document = $this->transformToElasticaDocument($object);
            $this->type->deleteById($document->getId());
        } catch (Exception $e) {
            $this->onError($e);
        }
    }

    /**
     * Inserts an array of objects in the type
     *
     * @param array of domain model objects
     **/
    public function insertMany(array $objects)
    {
        try {
            $documents = array();
            foreach ($objects as $object) {
                $documents[] = $this->transformToElasticaDocument($object);
            }
            $this->type->addDocuments($documents);
        } catch (Exception $e) {
            $this->onError($e);
        }
    }

    /**
     * Transforms an object to an elastica document
     *
     * @param object $object
     * @return Elastica_Document the elastica document
     */
    protected function transformToElasticaDocument($object)
    {
        return $this->transformer->transform($object, $this->mappingRegistry->getTypeFieldNames($this->type));
    }

    /**
     * What to do when an error occurs
     *
     * @param Exception
     **/
    protected function onError(Exception $exception)
    {
        if ($this->throwExceptions) {
            throw $exception;
        }

        if ($this->logger) {
            $message = sprintf('Elastica object persister failure (%s: %s)', get_class($exception), $exception->getMessage());
            $this->logger->warn($message);
        }
    }
}
