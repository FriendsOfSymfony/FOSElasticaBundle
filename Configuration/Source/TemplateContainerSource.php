<?php

/**
 * This file is part of the FOSElasticaBundle project.
 *
 * (c) Tim Nagel <tim@nagel.com.au>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Configuration\Source;

use FOS\ElasticaBundle\Configuration\IndexTemplateConfig;
use FOS\ElasticaBundle\Configuration\TypeConfig;

/**
 * Returns index and type configuration from the container.
 */
class TemplateContainerSource implements SourceInterface
{
    /**
     * The internal container representation of information.
     *
     * @var array
     */
    private $configArray;

    public function __construct(array $configArray)
    {
        $this->configArray = $configArray;
    }

    /**
     * Should return all configuration available from the data source.
     *
     * @return IndexTemplateConfig[]
     */
    public function getConfiguration()
    {
        $indexes = array();
        foreach ($this->configArray as $config) {
            $types = $this->getTypes($config);
            $index = new IndexTemplateConfig($config['name'], $types, array(
                'elasticSearchName' => $config['elasticsearch_name'],
                'settings' => $config['settings'],
                'template' => $config['template'],
            ));

            $indexes[$config['name']] = $index;
        }

        return $indexes;
    }

    /**
     * Builds TypeConfig objects for each type.
     *
     * @param array $config
     *
     * @return array
     */
    protected function getTypes($config)
    {
        $types = array();

        if (isset($config['types'])) {
            foreach ($config['types'] as $typeConfig) {
                $types[$typeConfig['name']] = new TypeConfig(
                    $typeConfig['name'],
                    $typeConfig['mapping'],
                    $typeConfig['config']
                );
            }
        }

        return $types;
    }
}
