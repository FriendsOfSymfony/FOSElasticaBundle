<?php

namespace FOS\ElasticaBundle\Transformer;

use FOS\ElasticaBundle\HybridResult;
use Elastica\Document;

/**
 * Holds a collection of transformers for an index wide transformation.
 *
 * @author Tim Nagel <tim@nagel.com.au>
 */
class ElasticaToModelTransformerCollection implements ElasticaToModelTransformerInterface
{
    /**
     * @var ElasticaToModelTransformerInterface[]
     */
    protected $transformers = array();

    public function __construct(array $transformers)
    {
        $this->transformers = $transformers;
    }

    public function getObjectClass()
    {
        return array_map(function (ElasticaToModelTransformerInterface $transformer) {
            return $transformer->getObjectClass();
        }, $this->transformers);
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifierField()
    {
        return array_map(function (ElasticaToModelTransformerInterface $transformer) {
            return $transformer->getIdentifierField();
        }, $this->transformers);
    }

    /**
     * @param Document[] $elasticaObjects
     *
     * @return array
     */
    public function transform(array $elasticaObjects)
    {
        $sorted = array();
        foreach ($elasticaObjects as $object) {
            $sorted[$object->getType()][] = $object;
        }

        $transformed = array();
        foreach ($sorted as $type => $objects) {
            $transformedObjects = $this->transformers[$type]->transform($objects);
            $identifierGetter = 'get'.ucfirst($this->transformers[$type]->getIdentifierField());
            $transformed[$type] = array_combine(
                array_map(
                    function ($o) use ($identifierGetter) {
                        return $o->$identifierGetter();
                    },
                    $transformedObjects
                ),
                $transformedObjects
            );
        }

        $result = array();
        foreach ($elasticaObjects as $object) {
            if (array_key_exists($object->getId(), $transformed[$object->getType()])) {
                $result[] = $transformed[$object->getType()][$object->getId()];
            }
        }

        return $result;
    }

    public function hybridTransform(array $elasticaObjects)
    {
        $objects = $this->transform($elasticaObjects);

        $result = array();
        for ($i = 0, $j = count($elasticaObjects); $i < $j; $i++) {
            $result[] = new HybridResult($elasticaObjects[$i], $objects[$i]);
        }

        return $result;
    }
}
