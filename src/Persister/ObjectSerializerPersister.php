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
use Elastica\Type;
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
     * @param Type                                $type
     * @param ModelToElasticaTransformerInterface $transformer
     * @param string                              $objectClass
     * @param callable                            $serializer
     */
    public function __construct(Type $type, ModelToElasticaTransformerInterface $transformer, $objectClass, $serializer)
    {
        parent::__construct($type, $transformer, $objectClass, []);

        $this->serializer = $serializer;
    }

    /**
     * Transforms an object to an elastica document
     * with just the identifier set.
     *
     * @param object $object
     *
     * @return Document the elastica document
     */
    public function transformToElasticaDocument($object)
    {
        $document = $this->transformer->transform($object, []);

        $data = call_user_func($this->serializer, $object);
        $document->setData($data);

        return $document;
    }
}
