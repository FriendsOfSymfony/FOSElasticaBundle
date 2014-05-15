<?php

namespace FOS\ElasticaBundle\Transformer;

use FOS\ElasticaBundle\Type\TypeConfiguration;

interface ResultTransformerInterface
{
    /**
     * Transforms Elastica results into Models.
     *
     * @param TypeConfiguration $configuration
     * @param array $results
     */
    public function transform(TypeConfiguration $configuration, $results);
}
