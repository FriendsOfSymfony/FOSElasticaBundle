<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <https://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Persister;

use Elastica\Document;
use Elastica\Index;
use FOS\ElasticaBundle\Transformer\ModelToElasticaTransformerInterface;

/**
 * Inserts, replaces and deletes single objects in an elastica type, making use
 * of elastica's serializer support to convert objects in to elastica documents.
 * Accepts domain model objects and passes them directly to elastica.
 *
 * @author Lea Haensenberber <lea.haensenberger@gmail.com>
 */
class ObjectSerializerPersister extends ObjectPersister
{
    protected $serializer;

    /**
     * @param callable $serializer
     */
    public function __construct(Index $index, ModelToElasticaTransformerInterface $transformer, string $objectClass, $serializer, array $options = [])
    {
        parent::__construct($index, $transformer, $objectClass, [], $options);

        $this->serializer = $serializer;
    }

    /**
     * Transforms an object to an elastica document
     * with just the identifier set.
     */
    public function transformToElasticaDocument(object $object): Document
    {
        $document = $this->transformer->transform($object, []);

        $data = \call_user_func($this->serializer, $object);
        $document->setData($data);

        return $document;
    }
}
