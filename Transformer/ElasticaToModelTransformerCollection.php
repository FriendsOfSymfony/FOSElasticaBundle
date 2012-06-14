<?php

namespace FOQ\ElasticaBundle\Transformer;

use FOQ\ElasticaBundle\HybridResult;
use Symfony\Component\Form\Util\PropertyPath;

/**
 * Holds a collection of transformers for an index wide transformation.
 *
 * @author Tim Nagel <tim@nagel.com.au>
 */
class ElasticaToModelTransformerCollection implements ElasticaToModelTransformerInterface
{
    protected $transformers = array();
    protected $options = array(
        'identifier' => 'id'
    );

    public function __construct(array $transformers, array $options)
    {
        $this->transformers = $transformers;
        $this->options = array_merge($this->options, $options);
    }

    public function getObjectClass()
    {
        return array_map(function ($transformer) {
            return $transformer->getObjectClass();
        }, $this->transformers);
    }

    public function transform(array $elasticaObjects)
    {
        $sorted = array();
        foreach ($elasticaObjects as $object) {
            $sorted[$object->getType()][] = $object;
        }

        $identifierProperty = new PropertyPath($this->options['identifier']);

        $transformed = array();
        foreach ($sorted AS $type => $objects) {
            $transformedObjects = $this->transformers[$type]->transform($objects);
            $transformed[$type] = array_combine(array_map(function($o) use ($identifierProperty) {return $identifierProperty->getValue($o);},$transformedObjects),$transformedObjects);
        }

        $result = array();
        foreach ($elasticaObjects as $object) {
            $result[] = $transformed[$object->getType()][$object->getId()];
        }

        return $result;
    }

    public function hybridTransform(array $elasticaObjects)
    {
        $objects = $this->transform($elasticaObjects);

        $result = array();
        for ($i = 0; $i < count($elasticaObjects); $i++) {
            $result[] = new HybridResult($elasticaObjects[$i], $objects[$i]);
        }

        return $result;
    }

    protected function getTypeToClassMap()
    {
        return array_map(function ($transformer) {
            return $transformer->getObjectClass();
        }, $this->transformers);
    }
}