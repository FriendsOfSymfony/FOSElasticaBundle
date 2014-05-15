<?php

namespace FOS\ElasticaBundle\Transformer;

use FOS\ElasticaBundle\Elastica\TransformingResult;

/**
 * Transforms results from an index wide query with multiple types.
 *
 * @author Tim Nagel <tim@nagel.com.au>
 */
class CombinedResultTransformer
{
    /**
     * @var \FOS\ElasticaBundle\Type\TypeConfiguration
     */
    private $configurations;

    /**
     * @var ResultTransformerInterface
     */
    private $transformer;

    /**
     * @param \FOS\ElasticaBundle\Type\TypeConfiguration[] $configurations
     * @param ResultTransformerInterface $transformer
     */
    public function __construct(array $configurations, ResultTransformerInterface $transformer)
    {
        $this->configurations = $configurations;
        $this->transformer = $transformer;
    }

    /**
     * Transforms Elastica results into Models.
     *
     * @param TransformingResult[] $results
     * @return object[]
     */
    public function transform($results)
    {
        $grouped = array();

        foreach ($results as $result) {
            $grouped[$result->getType()][] = $result;
        }

        foreach ($grouped as $type => $group) {
            $this->transformer->transform($this->getConfiguration($type), $group);
        }
    }

    /**
     * Retrieves the transformer for a given type.
     *
     * @param string $type
     * @return \FOS\ElasticaBundle\Type\TypeConfiguration
     * @throws \InvalidArgumentException
     */
    private function getConfiguration($type)
    {
        if (!array_key_exists($type, $this->configurations)) {
            throw new \InvalidArgumentException(sprintf(
                'Configuration for type "%s" is not registered with this combined transformer.',
                $type
            ));
        }

        return $this->configurations[$type];
    }
}
