<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Persister;

use Elastica\Document;
use Elastica\Exception\BulkException;
use Elastica\Type;
use FOS\ElasticaBundle\Transformer\ModelToElasticaTransformerInterface;
use Psr\Log\LoggerInterface;

/**
 * Inserts, replaces and deletes single documents in an elastica type
 * Accepts domain model objects and converts them to elastica documents.
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
    private $options;

    /**
     * @param Type                                $type
     * @param ModelToElasticaTransformerInterface $transformer
     * @param string                              $objectClass
     * @param array                               $fields
     */
    public function __construct(Type $type, ModelToElasticaTransformerInterface $transformer, $objectClass, array $fields, array $options = [])
    {
        $this->type = $type;
        $this->transformer = $transformer;
        $this->objectClass = $objectClass;
        $this->fields = $fields;
        $this->options = $options;
    }

    /**
     * {@inheritdoc}
     */
    public function handlesObject($object)
    {
        return $object instanceof $this->objectClass;
    }

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function insertOne($object)
    {
        $this->insertMany([$object]);
    }

    /**
     * {@inheritdoc}
     */
    public function replaceOne($object)
    {
        $this->replaceMany([$object]);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteOne($object)
    {
        $this->deleteMany([$object]);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($id, $routing = false)
    {
        $this->deleteManyByIdentifiers([$id], $routing);
    }

    /**
     * {@inheritdoc}
     */
    public function insertMany(array $objects)
    {
        $documents = [];
        foreach ($objects as $object) {
            $documents[] = $this->transformToElasticaDocument($object);
        }
        try {
            $this->type->addDocuments($documents, $this->options);
        } catch (BulkException $e) {
            $this->log($e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function replaceMany(array $objects)
    {
        $documents = [];
        foreach ($objects as $object) {
            $document = $this->transformToElasticaDocument($object);
            $document->setDocAsUpsert(true);
            $documents[] = $document;
        }

        try {
            $this->type->updateDocuments($documents, $this->options);
        } catch (BulkException $e) {
            $this->log($e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function deleteMany(array $objects)
    {
        $documents = [];
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
     * {@inheritdoc}
     */
    public function deleteManyByIdentifiers(array $identifiers, $routing = false)
    {
        try {
            $this->type->deleteIds($identifiers, $routing);
        } catch (BulkException $e) {
            $this->log($e);
        }
    }

    /**
     * Transforms an object to an elastica document.
     *
     * @param object $object
     *
     * @return Document the elastica document
     */
    public function transformToElasticaDocument($object)
    {
        return $this->transformer->transform($object, $this->fields);
    }

    /**
     * Log exception if logger defined for persister belonging to the current listener, otherwise re-throw.
     *
     * @param BulkException $e
     *
     * @throws BulkException
     */
    private function log(BulkException $e)
    {
        if (!$this->logger) {
            throw $e;
        }

        $this->logger->error($e);
    }
}
