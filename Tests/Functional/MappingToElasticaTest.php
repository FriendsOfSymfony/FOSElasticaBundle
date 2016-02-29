<?php

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
        $client = $this->createClient(array('test_case' => 'Basic'));
        $resetter = $this->getResetter($client);
        $resetter->resetIndex('index');

        $type = $this->getType($client);
        $mapping = $type->getMapping();

        $this->assertNotEmpty($mapping, 'Mapping was populated');

        $type = $this->getType($client, 'type');
        $mapping = $type->getMapping();
        $this->assertEquals('parent', $mapping['type']['_parent']['type']);

        $this->assertEquals('strict', $mapping['type']['dynamic']);
        $this->assertArrayHasKey('dynamic', $mapping['type']['properties']['dynamic_allowed']);
        $this->assertEquals('true', $mapping['type']['properties']['dynamic_allowed']['dynamic']);

        $parent = $this->getType($client, 'parent');
        $mapping = $parent->getMapping();

        $this->assertEquals('my_analyzer', $mapping['parent']['index_analyzer']);
        $this->assertEquals('whitespace', $mapping['parent']['search_analyzer']);
    }


    public function testORMResetIndexAddsMappings()
    {
        $client = $this->createClient(array('test_case' => 'ORM'));
        $resetter = $this->getResetter($client);
        $resetter->resetIndex('index');

        $type = $this->getType($client);
        $mapping = $type->getMapping();

        $this->assertNotEmpty($mapping, 'Mapping was populated');
    }

    public function testMappingIteratorToArrayField()
    {
        $client = $this->createClient(array('test_case' => 'ORM'));
        $persister = $client->getContainer()->get('fos_elastica.object_persister.index.type');

        $object = new TypeObj();
        $object->id = 1;
        $object->coll = new \ArrayIterator(array('foo', 'bar'));
        $persister->insertOne($object);

        $object->coll = new \ArrayIterator(array('foo', 'bar', 'bazz'));
        $object->coll->offsetUnset(1);

        $persister->replaceOne($object);
    }

    /**
     * @param Client $client
     *
     * @return \FOS\ElasticaBundle\Resetter $resetter
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
