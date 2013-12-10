<?php

namespace FOS\ElasticaBundle\Transformer;

use Symfony\Component\Form\Util\PropertyPath;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * Maps Elastica documents with Doctrine objects
 * This mapper assumes an exact match between
 * elastica documents ids and doctrine object ids
 */
class ModelToElasticaAutoTransformer implements ModelToElasticaTransformerInterface
{
    /**
     * Optional parameters
     *
     * @var array
     */
    protected $options = array(
        'identifier' => 'id'
    );

    /**
     * PropertyAccessor instance (will be used if available)
     *
     * @var PropertyAccessorInterface
     */
    private $propertyAccessor;

    /**
     * Instanciates a new Mapper
     *
     * @param array $options
     */
    public function __construct(array $options = array())
    {
        $this->options = array_merge($this->options, $options);
    }

    /**
     * Set the PropertyAccessor
     *
     * @param PropertyAccessorInterface $propertyAccessor
     */
    public function setPropertyAccessor(PropertyAccessorInterface $propertyAccessor = null)
    {
        $this->propertyAccessor = $propertyAccessor;
    }

    /**
     * Transforms an object into an elastica object having the required keys
     *
     * @param object $object the object to convert
     * @param array  $fields the keys we want to have in the returned array
     *
     * @return \Elastica_Document
     **/
    public function transform($object, array $fields)
    {
        $identifier = $this->getPropertyValue($object, $this->options['identifier']);
        $document = new \Elastica_Document($identifier);

        foreach ($fields as $key => $mapping) {
            $value = $this->getPropertyValue($object, $key);

            if (isset($mapping['_parent']['identifier'])) {
                /* $value is the parent. Read its identifier and set that as the
                 * document's parent.
                 */
                $document->setParent($this->getPropertyValue($value, $mapping['_parent']['identifier']));
                continue;
            }

            if (isset($mapping['type']) && in_array($mapping['type'], array('nested', 'object'))) {
                /* $value is a nested document or object. Transform $value into
                 * an array of documents, respective the mapped properties.
                 */
                $document->add($key, $this->transformNested($value, $mapping['properties'], $document));
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

            $document->add($key, $this->normalizeValue($value));
        }

        return $document;
    }

    /**
     * Get the value of an object property.
     *
     * This method will use Symfony 2.2's PropertyAccessor if it is available.
     *
     * @param object $object
     * @param string $property
     * @return mixed
     */
    protected function getPropertyValue($object, $property)
    {
        if (isset($this->propertyAccessor)) {
            return $this->propertyAccessor->getValue($object, $property);
        }

        $propertyPath = new PropertyPath($property);

        return $propertyPath->getValue($object);
    }

    /**
     * Transform a nested document or an object property into an array of
     * Elastica_Document objects.
     *
     * @param array|\Traversable $objects the objects to convert
     * @param array $fields the keys we want to have in the returned array
     * @return array
     * @throws \InvalidArgumentException if $objects is not an array or Traversable
     */
    protected function transformNested($objects, array $fields)
    {
        if (!(is_array($objects) || $objects instanceof \Traversable)) {
            throw new \InvalidArgumentException('$objects parameter must be an array or Traversable.');
        }

        $documents = array();

        foreach ($objects as $object) {
            $document = $this->transform($object, $fields);
            $documents[] = $document->getData();
        }

        return $documents;
    }

    /**
     * Attempts to convert any type to a string or an array of strings
     *
     * @param mixed $value
     *
     * @return string|array
     */
    protected function normalizeValue($value)
    {
        $normalize = function(&$v) {
            if ($v instanceof \DateTime) {
                $v = $v->format('c');
            } elseif ($v !== null && !is_scalar($v)) {
                $v = (string) $v;
            }
        };

        if (is_array($value) || $value instanceof \Traversable) {
            $value = is_array($value) ? $value : iterator_to_array($value);
            array_walk_recursive($value, $normalize);
        } else {
            $normalize($value);
        }

        return $value;
    }
}
