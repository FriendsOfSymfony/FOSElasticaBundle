<?php

namespace FOS\ElasticaBundle\Transformer;

/**
 * Maps Elastica documents with model objects.
 */
interface HighlightableModelInterface
{
    /**
     * Set ElasticSearch highlight data.
     *
     * @param array $highlights array of highlight strings
     */
    public function setElasticHighlights(array $highlights);
}
