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

use Symfony\Bundle\FrameworkBundle\Client;

/**
 * @group functional
 */
class MappingToElasticaTest extends WebTestCase
{
    public function testResetIndexAddsMappings()
    {
        $client = $this->createClient(['test_case' => 'Basic']);
        $resetter = $this->getResetter($client);
        $resetter->resetIndex('index');

        $type = $this->getType($client);
        $mapping = $type->getMapping();

        $this->assertNotEmpty($mapping, 'Mapping was populated');

        $type = $this->getType($client, 'type');
        $mapping = $type->getMapping();
        $this->assertSame('parent', $mapping['type']['_parent']['type']);

        $this->assertSame('strict', $mapping['type']['dynamic']);
        $this->assertArrayHasKey('dynamic', $mapping['type']['properties']['dynamic_allowed']);
        $this->assertSame('true', $mapping['type']['properties']['dynamic_allowed']['dynamic']);
    }

    public function testResetType()
    {
        $client = $this->createClient(['test_case' => 'Basic']);
        $resetter = $this->getResetter($client);
        $resetter->resetIndexType('index', 'type');

        $type = $this->getType($client);
        $mapping = $type->getMapping();

        $this->assertNotEmpty($mapping, 'Mapping was populated');
        $this->assertFalse($mapping['type']['date_detection']);
        $this->assertTrue($mapping['type']['numeric_detection']);
        $this->assertSame(['yyyy-MM-dd'], $mapping['type']['dynamic_date_formats']);
    }

    public function testORMResetIndexAddsMappings()
    {
        $client = $this->createClient(['test_case' => 'ORM']);
        $resetter = $this->getResetter($client);
        $resetter->resetIndex('index');

        $type = $this->getType($client);
        $mapping = $type->getMapping();

        $this->assertNotEmpty($mapping, 'Mapping was populated');
    }

    public function testORMResetType()
    {
        $client = $this->createClient(['test_case' => 'ORM']);
        $resetter = $this->getResetter($client);
        $resetter->resetIndexType('index', 'type');

        $type = $this->getType($client);
        $mapping = $type->getMapping();

        $this->assertNotEmpty($mapping, 'Mapping was populated');
    }

    public function testMappingIteratorToArrayField()
    {
        $client = $this->createClient(['test_case' => 'ORM']);
        $persister = $client->getContainer()->get('fos_elastica.object_persister.index.type');

        $object = new TypeObj();
        $object->id = 1;
        $object->coll = new \ArrayIterator(['foo', 'bar']);
        $persister->insertOne($object);

        $object->coll = new \ArrayIterator(['foo', 'bar', 'bazz']);
        $object->coll->offsetUnset(1);

        $persister->replaceOne($object);
    }

    /**
     * @param Client $client
     *
     * @return \FOS\ElasticaBundle\Index\Resetter $resetter
     */
    private function getResetter(Client $client)
    {
        return $client->getContainer()->get('fos_elastica.resetter');
    }

    /**
     * @param Client $client
     * @param string $type
     *
     * @return \Elastica\Type
     */
    private function getType(Client $client, $type = 'type')
    {
        return $client->getContainer()->get('fos_elastica.index.index.'.$type);
    }

    protected function setUp()
    {
        parent::setUp();

        $this->deleteTmpDir('Basic');
        $this->deleteTmpDir('ORM');
    }

    protected function tearDown()
    {
        parent::tearDown();

        $this->deleteTmpDir('Basic');
        $this->deleteTmpDir('ORM');
    }
}
