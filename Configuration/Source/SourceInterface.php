<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * This file is part of the FOSElasticaBundle project.
 *
 * (c) Tim Nagel <tim@nagel.com.au>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Configuration\Source;

/**
 * Represents a source of index and type information (ie, the Container configuration or
 * annotations).
 */
interface SourceInterface
{
    /**
     * Should return all configuration available from the data source.
     *
     * @return \FOS\ElasticaBundle\Configuration\IndexConfig[]
     */
    public function getConfiguration();
}
