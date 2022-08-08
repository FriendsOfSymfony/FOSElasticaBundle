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

/**
 * @phpstan-import-type TMapping from IndexConfigInterface
 */
final class PostIndexMappingBuildEvent extends AbstractIndexEvent
{
    /**
     * @var IndexConfigInterface
     */
    private $indexConfig;

    /**
     * @var array
     * @phpstan-var TMapping
     */
    private $mapping;

    /**
     * @phpstan-param TMapping $mapping
     */
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

    /**
     * @phpstan-return TMapping
     */
    public function getMapping(): array
    {
        return $this->mapping;
    }

    /**
     * @phpstan-param TMapping $mapping
     */
    public function setMapping(array $mapping): void
    {
        $this->mapping = $mapping;
    }
}
