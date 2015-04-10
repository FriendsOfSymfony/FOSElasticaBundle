<?php

namespace FOS\ElasticaBundle\Transformer;

use FOS\ElasticaBundle\Event\TransformEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Elastica\Document;

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
    protected $options = array(
        'identifier' => 'id',
    );

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
    public function __construct(array $options = array(), EventDispatcherInterface $dispatcher = null)
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
        $document = new Document($identifier);

        foreach ($fields as $key => $mapping) {
            if ($key == '_parent') {
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

            if (isset($mapping['type']) && in_array($mapping['type'], array('nested', 'object')) && isset($mapping['properties']) && !empty($mapping['properties'])) {
                /* $value is a nested document or object. Transform $value into
                 * an array of documents, respective the mapped properties.
                 */
                $document->set($key, $this->transformNested($value, $mapping['properties']));

                continue;
            }

            if (isset($mapping['type']) && $mapping['type'] == 'attachment') {
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
            $documents = array();
            foreach ($objects as $object) {
                $document = $this->transform($object, $fields);
                $documents[] = $document->getData();
            }

            return $documents;
        } elseif (null !== $objects) {
            $document = $this->transform($objects, $fields);

            return $document->getData();
        }

        return array();
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
            if ($v instanceof \DateTime) {
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
}
