<?php

/**
 * This file is part of the FOSElasticaBundle project.
 *
 * (c) Tim Nagel <tim@nagel.com.au>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Configuration;

class TypeConfig
{
    /**
     * @var array
     */
    private $config;

    /**
     * @var string
     */
    private $name;

    public function __construct($name, array $config, array $prototype)
    {
        $this->config = $config;
        $this->name = $name;
    }
}
