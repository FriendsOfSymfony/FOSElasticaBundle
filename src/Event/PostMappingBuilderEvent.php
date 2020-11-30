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
use Symfony\Contracts\EventDispatcher\Event;

class PostMappingBuilderEvent extends Event
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
    }

    public function getIndexConfig(): IndexConfigInterface
    {
        return $this->indexConfig;
    }

    public function getMapping(): array
    {
        return $this->mapping;
    }
}
