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

/**
 * Indicates that the model should have elastica highlights injected.
 */
interface HighlightableModelInterface
{
    /**
     * Returns a unique identifier for the model.
     *
     * @return mixed
     */
    public function getId();

    /**
     * Set ElasticSearch highlight data.
     *
     * @param array $highlights array of highlight strings
     */
    public function setElasticHighlights(array $highlights);
}
