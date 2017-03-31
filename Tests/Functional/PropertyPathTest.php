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

use Elastica\Query\Match;

/**
 * @group functional
 */
class PropertyPathTest extends WebTestCase
{
    public function testContainerSource()
    {
        $client = $this->createClient(['test_case' => 'ORM']);
        /** @var \FOS\ElasticaBundle\Persister\ObjectPersister $persister */
        $persister = $client->getContainer()->get('fos_elastica.object_persister.index.property_paths_type');
        $obj = new TypeObj();
        $obj->coll = 'Hello';
        $persister->insertOne($obj);

        /** @var \Elastica\Index $index */
        $index = $client->getContainer()->get('fos_elastica.index.index');
        $index->refresh();

        $query = new Match();
        $query->setField('something', 'Hello');
        $search = $index->createSearch($query);

        $this->assertSame(1, $search->count());
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
