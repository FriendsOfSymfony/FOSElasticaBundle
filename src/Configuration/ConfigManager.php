<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Configuration;

/**
 * Central manager for index and type configuration.
 */
class ConfigManager implements ManagerInterface
{
    /**
     * @var IndexConfig[]
     */
    private $indexes = [];

    /**
     * @param Source\SourceInterface[] $sources
     */
    public function __construct(array $sources)
    {
        foreach ($sources as $source) {
            $this->indexes = array_merge($source->getConfiguration(), $this->indexes);
        }
    }

    public function getIndexConfiguration(string $indexName): IndexConfigInterface
    {
        if (!$this->hasIndexConfiguration($indexName)) {
            throw new \InvalidArgumentException(sprintf('Index with name "%s" is not configured.', $indexName));
        }

        return $this->indexes[$indexName];
    }

    public function getIndexNames(): array
    {
        return array_keys($this->indexes);
    }

    public function hasIndexConfiguration(string $indexName): bool
    {
        return isset($this->indexes[$indexName]);
    }
}
