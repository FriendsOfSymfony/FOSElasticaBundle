<?php

namespace FOS\ElasticaBundle\Transformer;

use FOS\ElasticaBundle\Type\TypeConfigurationInterface;

interface ResultTransformerInterface
{
    /**
     * Transforms Elastica results into Models.
     *
     * @param TypeConfigurationInterface $configuration
     * @param array $results
     */
    public function transform(TypeConfigurationInterface $configuration, $results);
}
