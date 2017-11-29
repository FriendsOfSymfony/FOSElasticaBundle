<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Tests\Functional;

class PersistenceRepositoryTest extends WebTestCase
{
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

    public function testRepositoryShouldBeSetCorrectly()
    {
        $client = $this->createClient(['test_case' => 'ORM']);

        $repository = $client->getContainer()->get('fos_elastica.manager.orm')
            ->getRepository('FOS\ElasticaBundle\Tests\Functional\TypeObject');

        $this->assertNotNull($repository);
        $this->assertSame('FOS\ElasticaBundle\Tests\Functional\TypeObjectRepository', get_class($repository));
    }
}
