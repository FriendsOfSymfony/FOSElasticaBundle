<?php

namespace FOQ\ElasticaBundle\Transformer;

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
        $order = array();
        foreach ($elasticaObjects as $object) {
            $sorted[$object->getType()][] = $object;
            $order[] = sprintf('%s-%s', $object->getType(), $object->getId());
        }

        $transformed = array();
        foreach ($sorted AS $type => $objects) {
            $transformed = array_merge($transformed, $this->transformers[$type]->transform($objects));
        }

        $positions = array_flip($order);
        $identifierGetter = 'get' . ucfirst($this->options['identifier']);
        $classMap = $this->getTypeToClassMap();

        usort($transformed, function($a, $b) use ($positions, $identifierGetter, $classMap)
        {
            $aType = array_search(get_class($a), $classMap);
            $bType = array_search(get_class($b), $classMap);

            return $positions["{$aType}-{$a->$identifierGetter()}"] > $positions["{$bType}-{$b->$identifierGetter()}"];
        });

        return $transformed;
    }

    protected function getTypeToClassMap()
    {
        return array_map(function ($transformer) {
            return $transformer->getObjectClass();
        }, $this->transformers);
    }
}