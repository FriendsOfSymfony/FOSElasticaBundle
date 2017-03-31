<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * This file is part of the FOSElasticaBundle project.
 *
 * (c) Tim Nagel <tim@nagel.com.au>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace FOS\ElasticaBundle\Tests\Functional;

/**
 * @group functional
 */
class ClientTest extends WebTestCase
{
    public function testContainerSource()
    {
        $client = $this->createClient(['test_case' => 'Basic']);

        $es = $client->getContainer()->get('fos_elastica.client.default');
        $this->assertInstanceOf('Elastica\\Connection\\Strategy\\RoundRobin', $es->getConnectionStrategy());

        $es = $client->getContainer()->get('fos_elastica.client.second_server');
        $this->assertInstanceOf('Elastica\\Connection\\Strategy\\RoundRobin', $es->getConnectionStrategy());

        $es = $client->getContainer()->get('fos_elastica.client.third');
        $this->assertInstanceOf('Elastica\\Connection\\Strategy\\Simple', $es->getConnectionStrategy());
    }

    protected function setUp()
    {
        parent::setUp();

        $this->deleteTmpDir('Basic');
    }

    protected function tearDown()
    {
        parent::tearDown();

        $this->deleteTmpDir('Basic');
    }
}
