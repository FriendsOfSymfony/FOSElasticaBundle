<?php

namespace FOQ\ElasticaBundle\Transformer;

use Symfony\Component\Form\Util\PropertyPath;

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
     * Instanciates a new Mapper
     *
     * @param array $options
     */
    public function __construct(array $options = array())
    {
        $this->options = array_merge($this->options, $options);
    }

    /**
     * Transforms an object into an elastica object having the required keys
     *
     * @param object $object the object to convert
     * @param array  $fields the keys we want to have in the returned array
     *
     * @return Elastica_Document
     **/
    public function transform($object, array $fields)
    {
        $identifierProperty = new PropertyPath($this->options['identifier']);
        $identifier         = $identifierProperty->getValue($object);
        $document           = new \Elastica_Document($identifier);
        foreach ($fields as $key => $mapping) {
            $property = new PropertyPath($key);
            if (!empty($mapping['_parent']) && $mapping['_parent'] !== '~') {
                $parent             = $property->getValue($object);
                $identifierProperty = new PropertyPath($mapping['_parent']['identifier']);
                $document->setParent($identifierProperty->getValue($parent));
            } else if (isset($mapping['type']) && in_array($mapping['type'], array('nested', 'object'))) {
                $submapping     = $mapping['properties'];
                $subcollection  = $property->getValue($object);
                $document->add($key, $this->transformNested($subcollection, $submapping, $document));
            } else if (isset($mapping['type']) && $mapping['type'] == 'multi_field') {
                throw new \Exception('Please implement me !');
            } else if (isset($mapping['type']) && $mapping['type'] == 'attachment') {
                $attachment = $property->getValue($object);
                if ($attachment instanceof \SplFileInfo) {
                    $document->addFile($key, $attachment->getPathName());
                } else {
                    $document->addFileContent($key, $attachment);
                }
            } else {
                $document->add($key, $this->normalizeValue($property->getValue($object)));
            }
        }
        return $document;
    }

    /**
     * transform a nested document or an object property into an array of ElasticaDocument
     *
     * @param array $objects    the object to convert
     * @param array $fields     the keys we want to have in the returned array
     * @param Elastica_Document $parent the parent document
     * @return array
     */
    protected function transformNested($objects, array $fields, $parent)
    {
        $documents = array();
        foreach($objects as $object) {
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
        $normalizeValue = function(&$v)
        {
            if ($v instanceof \DateTime) {
                $v = (int)$v->format('U');
            } elseif (!is_scalar($v) && !is_null($v)) {
                $v = (string)$v;
            }
        };

        if (is_array($value) || $value instanceof \Traversable || $value instanceof \ArrayAccess) {
            $value = is_array($value) ? $value : iterator_to_array($value);
            array_walk_recursive($value, $normalizeValue);
        } else {
            $normalizeValue($value);
        }

        return $value;
    }

}
