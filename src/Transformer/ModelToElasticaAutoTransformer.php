<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Transformer;

use Elastica\Document;
use FOS\ElasticaBundle\Event\TransformEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * Maps Elastica documents with Doctrine objects
 * This mapper assumes an exact match between
 * elastica documents ids and doctrine object ids.
 */
class ModelToElasticaAutoTransformer implements ModelToElasticaTransformerInterface
{
    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * Optional parameters.
     *
     * @var array
     */
    protected $options = [
        'identifier' => 'id',
    ];

    /**
     * PropertyAccessor instance.
     *
     * @var PropertyAccessorInterface
     */
    protected $propertyAccessor;

    /**
     * Instanciates a new Mapper.
     *
     * @param array                    $options
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(array $options = [], EventDispatcherInterface $dispatcher = null)
    {
        $this->options = array_merge($this->options, $options);
        $this->dispatcher = $dispatcher;
    }

    /**
     * Set the PropertyAccessor.
     *
     * @param PropertyAccessorInterface $propertyAccessor
     */
    public function setPropertyAccessor(PropertyAccessorInterface $propertyAccessor)
    {
        $this->propertyAccessor = $propertyAccessor;
    }

    /**
     * Transforms an object into an elastica object having the required keys.
     *
     * @param object $object the object to convert
     * @param array  $fields the keys we want to have in the returned array
     *
     * @return Document
     **/
    public function transform($object, array $fields)
    {
        $identifier = $this->propertyAccessor->getValue($object, $this->options['identifier']);
        if ($identifier && !is_scalar($identifier)) {
            $identifier = (string) $identifier;
        }

        return $this->transformObjectToDocument($object, $fields, $identifier);
    }

    /**
     * transform a nested document or an object property into an array of ElasticaDocument.
     *
     * @param array|\Traversable|\ArrayAccess $objects the object to convert
     * @param array                           $fields  the keys we want to have in the returned array
     *
     * @return array
     */
    protected function transformNested($objects, array $fields)
    {
        if (is_array($objects) || $objects instanceof \Traversable || $objects instanceof \ArrayAccess) {
            $documents = [];
            foreach ($objects as $object) {
                $document = $this->transformObjectToDocument($object, $fields);
                $documents[] = $document->getData();
            }

            return $documents;
        } elseif (null !== $objects) {
            $document = $this->transformObjectToDocument($objects, $fields);

            return $document->getData();
        }

        return [];
    }

    /**
     * Attempts to convert any type to a string or an array of strings.
     *
     * @param mixed $value
     *
     * @return string|array
     */
    protected function normalizeValue($value)
    {
        $normalizeValue = function (&$v) {
            if ($v instanceof \DateTimeInterface) {
                $v = $v->format('c');
            } elseif (!is_scalar($v) && !is_null($v)) {
                $v = (string) $v;
            }
        };

        if (is_array($value) || $value instanceof \Traversable || $value instanceof \ArrayAccess) {
            $value = is_array($value) ? $value : iterator_to_array($value, false);
            array_walk_recursive($value, $normalizeValue);
        } else {
            $normalizeValue($value);
        }

        return $value;
    }

    /**
     * Transforms the given object to an elastica document.
     *
     * @param object $object     the object to convert
     * @param array  $fields     the keys we want to have in the returned array
     * @param string $identifier the identifier for the new document
     *
     * @return Document
     */
    protected function transformObjectToDocument($object, array $fields, $identifier = '')
    {
        $document = new Document($identifier, [], '', $this->options['index']);

        if ($this->dispatcher) {
            $event = new TransformEvent($document, $fields, $object);
            $this->dispatcher->dispatch(TransformEvent::PRE_TRANSFORM, $event);

            $document = $event->getDocument();
        }

        foreach ($fields as $key => $mapping) {
            if ('_parent' == $key) {
                $property = (null !== $mapping['property']) ? $mapping['property'] : $mapping['type'];
                $value = $this->propertyAccessor->getValue($object, $property);
                $document->setParent($this->propertyAccessor->getValue($value, $mapping['identifier']));

                continue;
            }

            $path = isset($mapping['property_path']) ?
                $mapping['property_path'] :
                $key;
            if (false === $path) {
                continue;
            }
            $value = $this->propertyAccessor->getValue($object, $path);

            if (isset($mapping['type']) && in_array(
                    $mapping['type'], ['nested', 'object']
                ) && isset($mapping['properties']) && !empty($mapping['properties'])
            ) {
                /* $value is a nested document or object. Transform $value into
                 * an array of documents, respective the mapped properties.
                 */
                $document->set($key, $this->transformNested($value, $mapping['properties']));

                continue;
            }

            if (isset($mapping['type']) && 'attachment' == $mapping['type']) {
                // $value is an attachment. Add it to the document.
                if ($value instanceof \SplFileInfo) {
                    $document->addFile($key, $value->getPathName());
                } else {
                    $document->addFileContent($key, $value);
                }

                continue;
            }

            $document->set($key, $this->normalizeValue($value));
        }

        if ($this->dispatcher) {
            $event = new TransformEvent($document, $fields, $object);
            $this->dispatcher->dispatch(TransformEvent::POST_TRANSFORM, $event);

            $document = $event->getDocument();
        }

        return $document;
    }
}
