<?php

namespace FOQ\ElasticaBundle\Transformer;

/**
 * Maps Elastica documents with model objects
 */
interface HighlightableModelInterface
{
    /**
     * Set ElasticSearch highlight data.
     *
     * @param array of highlight strings
     */
    public function setElasticHighlights(array $highlights);
}
