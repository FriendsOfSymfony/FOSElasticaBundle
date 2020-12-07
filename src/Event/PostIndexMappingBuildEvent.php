<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <https://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Event;

use FOS\ElasticaBundle\Configuration\IndexConfigInterface;

final class PostIndexMappingBuildEvent extends AbstractIndexEvent
{
    /**
     * @var IndexConfigInterface
     */
    private $indexConfig;

    /**
     * @var array
     */
    private $mapping;

    public function __construct(IndexConfigInterface $indexConfig, array $mapping)
    {
        $this->indexConfig = $indexConfig;
        $this->mapping = $mapping;

        parent::__construct($indexConfig->getName());
    }

    public function getIndexConfig(): IndexConfigInterface
    {
        return $this->indexConfig;
    }

    public function getMapping(): array
    {
        return $this->mapping;
    }

    public function setMapping(array $mapping): void
    {
        $this->mapping = $mapping;
    }
}
