<?php declare(strict_types=1);

namespace FOS\ElasticaBundle\Elastica\NodePool;

use Elastic\Transport\NodePool\Resurrect\NoResurrect;
use Elastic\Transport\NodePool\Selector\RoundRobin;
use Elastic\Transport\NodePool\SimpleNodePool;

class RoundRobinNoResurrect
{
    public static function create(): SimpleNodePool
    {
        return new SimpleNodePool(
            new RoundRobin(),
            new NoResurrect()
        );
    }
}
