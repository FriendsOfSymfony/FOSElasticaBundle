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
use Elastica\Index;
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
    protected $index;
    protected $transformer;
    protected $objectClass;
    protected $fields;
    protected $logger;
    private $options;

    public function __construct(Index $index, ModelToElasticaTransformerInterface $transformer, string $objectClass, array $fields, array $options = [])
    {
        $this->index = $index;
        $this->transformer = $transformer;
        $this->objectClass = $objectClass;
        $this->fields = $fields;
        $this->options = $options;
    }

    /**
     * {@inheritdoc}
     */
    public function handlesObject($object): bool
    {
        return $object instanceof $this->objectClass;
    }

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
            $this->index->addDocuments($documents, $this->options);
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
            $this->index->updateDocuments($documents, $this->options);
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
            $this->index->deleteDocuments($documents);
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
            $this->index->getClient()->deleteIds($identifiers, $this->index->getName(), $routing);
        } catch (BulkException $e) {
            $this->log($e);
        }
    }

    /**
     * Transforms an object to an elastica document.
     */
    public function transformToElasticaDocument(object $object): Document
    {
        return $this->transformer->transform($object, $this->fields);
    }

    /**
     * Log exception if logger defined for persister belonging to the current listener, otherwise re-throw.
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
