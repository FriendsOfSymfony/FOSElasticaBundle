<?php

namespace FOS\ElasticaBundle\Persister;

use Elastica\Document;
use Elastica\Type;
use FOS\ElasticaBundle\Event\TransformEvent;
use FOS\ElasticaBundle\Transformer\ModelToElasticaTransformerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

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
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * @param Type $type
     * @param ModelToElasticaTransformerInterface $transformer
     * @param string $objectClass
     * @param callable $serializer
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(Type $type, ModelToElasticaTransformerInterface $transformer, $objectClass, $serializer,  EventDispatcherInterface $dispatcher)
    {
        parent::__construct($type, $transformer, $objectClass, array());

        $this->serializer = $serializer;
        $this->dispatcher = $dispatcher;
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
        $document = $this->transformer->transform($object, array());

        $data = call_user_func($this->serializer, $object);
        $document->setData($data);

        if ($this->dispatcher) {
            $event = new TransformEvent($document, array(), $object);
            $this->dispatcher->dispatch(TransformEvent::POST_TRANSFORM, $event);

            $document = $event->getDocument();
        }

        return $document;
    }
}
