<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Configuration\Source;

use FOS\ElasticaBundle\Configuration\IndexConfig;

/**
 * Returns index and type configuration from the container.
 */
class ContainerSource implements SourceInterface
{
    /**
     * The internal container representation of information.
     *
     * @var array
     */
    private $configArray;

    /**
     * @param array $configArray
     */
    public function __construct(array $configArray)
    {
        $this->configArray = $configArray;
    }

    /**
     * Should return all configuration available from the data source.
     *
     * @return IndexConfig[]
     */
    public function getConfiguration()
    {
        $indexes = [];
        foreach ($this->configArray as $config) {

            $index = new IndexConfig($config);
            $indexes[$config['name']] = $index;
        }

        return $indexes;
    }
}
