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

use FOS\ElasticaBundle\HybridResult;

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
    protected $transformers = [];

    /**
     * @param array $transformers
     */
    public function __construct(array $transformers)
    {
        $this->transformers = $transformers;
    }

    /**
     * {@inheritdoc}
     */
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
     * {@inheritdoc}
     */
    public function transform(array $elasticaObjects)
    {
        $sorted = [];
        foreach ($elasticaObjects as $object) {
            $sorted[$object->getType()][] = $object;
        }

        $transformed = [];
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

        $result = [];
        foreach ($elasticaObjects as $object) {
            if (array_key_exists((string) $object->getId(), $transformed[$object->getType()])) {
                $result[] = $transformed[$object->getType()][(string) $object->getId()];
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function hybridTransform(array $elasticaObjects)
    {
        $objects = $this->transform($elasticaObjects);

        $result = [];
        for ($i = 0, $j = count($elasticaObjects); $i < $j; ++$i) {
            if (!isset($objects[$i])) {
                continue;
            }
            $result[] = new HybridResult($elasticaObjects[$i], $objects[$i]);
        }

        return $result;
    }
}
