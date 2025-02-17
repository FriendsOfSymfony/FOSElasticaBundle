<?php declare(strict_types=1);

namespace FOS\ElasticaBundle\Elastica\NodePool;

use Elastic\Transport\NodePool\Resurrect\ElasticsearchResurrect;
use Elastic\Transport\NodePool\Selector\RoundRobin;
use Elastic\Transport\NodePool\SimpleNodePool;

class RoundRobinResurrect
{
    public static function create(): SimpleNodePool
    {
        return new SimpleNodePool(
            new RoundRobin(),
            new ElasticsearchResurrect()
        );
    }
}
