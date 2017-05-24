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

namespace FOS\ElasticaBundle\Configuration;

/**
 * Central manager for index and type configuration.
 */
interface ManagerInterface
{
    /**
     * Returns configuration for an index.
     *
     * @param $index
     *
     * @return IndexConfig
     */
    public function getIndexConfiguration($index);

    /**
     * Returns an array of known index names.
     *
     * @return array
     */
    public function getIndexNames();

    /**
     * Returns a type configuration.
     *
     * @param string $index
     * @param string $type
     *
     * @return TypeConfig
     */
    public function getTypeConfiguration($index, $type);
}
