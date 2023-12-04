<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <https://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Configuration\Source;

use FOS\ElasticaBundle\Configuration\IndexConfigInterface;
use FOS\ElasticaBundle\Configuration\IndexTemplateConfig;

/**
 * Returns index and type configuration from the container.
 *
 * @phpstan-import-type TConfig from IndexConfigInterface
 */
class TemplateContainerSource implements SourceInterface
{
    /**
     * The internal container representation of information.
     *
     * @var array
     *
     * @phpstan-var list<TConfig>
     */
    private $configArray;

    /**
     * @param list<TConfig> $configArray
     */
    public function __construct(array $configArray)
    {
        $this->configArray = $configArray;
    }

    /**
     * Should return all configuration available from the data source.
     *
     * @return IndexTemplateConfig[]
     *
     * @phpstan-return array<string, IndexTemplateConfig>
     */
    public function getConfiguration(): array
    {
        $indexes = [];
        foreach ($this->configArray as $config) {
            $index = new IndexTemplateConfig($config);

            $indexes[$config['name']] = $index;
        }

        return $indexes;
    }
}
