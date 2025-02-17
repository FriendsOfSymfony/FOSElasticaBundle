<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <https://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Tests\Functional;

use Elastic\Transport\NodePool\SimpleNodePool;
use Elastica\Connection\Strategy\RoundRobin;
use Elastica\Connection\Strategy\Simple;
use FOS\ElasticaBundle\Elastica\Client;

/**
 * @group functional
 *
 * @internal
 */
class ClientTest extends WebTestCase
{
    public function testContainerSource()
    {
        self::bootKernel(['test_case' => 'Basic']);

        /** @var Client $es */
        $es = self::getContainer()->get('fos_elastica.client.default');
        $transportConfig = $es->getConfig('transport_config');
        self::assertArrayHasKey('node_pool', $transportConfig);
        $this->assertInstanceOf(SimpleNodePool::class, $transportConfig['node_pool']);

        /** @var Client $es */
        $es = self::getContainer()->get('fos_elastica.client.second_server');
        $transportConfig = $es->getConfig('transport_config');
        self::assertArrayHasKey('node_pool', $transportConfig);
        $this->assertInstanceOf(SimpleNodePool::class, $transportConfig['node_pool']);

        /** @var Client $es */
        $es = self::getContainer()->get('fos_elastica.client.third');
        $transportConfig = $es->getConfig('transport_config');
        self::assertArrayHasKey('node_pool', $transportConfig);
        self::assertNull($transportConfig['node_pool']);
    }
}
