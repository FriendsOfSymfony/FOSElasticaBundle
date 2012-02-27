<?php

namespace FOQ\ElasticaBundle\Transformer;

use Elastica_Document;
use Traversable;
use ArrayAccess;
use RuntimeException;

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
        $this->options       = array_merge($this->options, $options);
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
        $class = get_class($object);
        $array = array();
        foreach ($fields as $key) {
            $getter = 'get'.ucfirst($key);
            if (!method_exists($class, $getter)) {
                throw new RuntimeException(sprintf('The getter %s::%s does not exist', $class, $getter));
            }
            $array[$key] = $this->normalizeValue($object->$getter());
        }
        $identifierGetter = 'get'.ucfirst($this->options['identifier']);
        $identifier = $object->$identifierGetter();

        return new Elastica_Document($identifier, $array);
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

        if (is_array($value) || $value instanceof Traversable || $value instanceof ArrayAccess) {
            $value = is_array($value) ? $value : iterator_to_array($value);
            array_walk_recursive($value, $normalizeValue);
        } else {
            $normalizeValue($value);
        }

        return $value;
    }

}
