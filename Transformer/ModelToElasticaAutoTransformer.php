<?php

namespace FOQ\ElasticaBundle\Transformer;

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
     * @param array $fields the keys we want to have in the returned array
     * @return Elastica_Document
     **/
    public function transform($object, array $fields)
    {
        $identifierGetter = 'get' . ucfirst($this->options['identifier']);
        $identifier = $object->$identifierGetter();
        $document =  new \Elastica_Document($identifier);
        foreach ($fields as $key => $mapping) {
            $getter = 'get'.ucfirst($key);
            if (!is_callable(array($object, $getter))) {
                throw new \RuntimeException(sprintf('The method %s::%s is not callable', get_class($object), $getter));
            }
            if (isset($mapping['type']) && $mapping['type'] == 'attachment') {
                $attachment = $object->$getter();
                if($attachment instanceof \SplFileInfo) {
                    $document->addFile($key, $attachment->getPathName());
                } else {
                    $document->addFileContent($key, $attachment);
                }
            } else {
                $document->add($key, $this->normalizeValue($object->$getter()));
            }
        }
        return $document;
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
        $normalizeValue = function(&$v) {
            if ($v instanceof \DateTime) {
                $v = (int) $v->format('U');
            } elseif (!is_scalar($v) && !is_null($v)) {
                $v = (string) $v;
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
