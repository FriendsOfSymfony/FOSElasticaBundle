<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <https://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Transformer;

/**
 * Indicates that the model should have elastica highlights injected.
 *
 * @phpstan-type THighlight = array<mixed>
 */
interface HighlightableModelInterface
{
    /**
     * Returns a unique identifier for the model.
     */
    public function getId();

    /**
     * Set ElasticSearch highlight data.
     *
     * @param array $highlights array of highlight strings
     *
     * @phpstan-param list<THighlight> $highlights
     *
     * @return void
     */
    public function setElasticHighlights(array $highlights);
}
