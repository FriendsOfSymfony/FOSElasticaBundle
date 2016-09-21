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

/**
 * @group functional
 */
class SerializerTest extends WebTestCase
{
    public function testMappingIteratorToArrayField()
    {
        $client = $this->createClient(array('test_case' => 'Serializer'));
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
     * Tests that the serialize_null configuration attribute works
     */
    public function testWithNullValues()
    {
        $client = $this->createClient(array('test_case' => 'Serializer'));
        $container = $client->getContainer();

        $disabledNullPersister = $container->get('fos_elastica.object_persister.index.type_serialize_null_disabled');
        $enabledNullPersister = $container->get('fos_elastica.object_persister.index.type_serialize_null_enabled');

        $object = new TypeObj();
        $object->id = 1;
        $object->field1 = null;
        $disabledNullPersister->insertOne($object);
        $enabledNullPersister->insertOne($object);

        // Tests that attributes with null values are not persisted into an Elasticsearch type without the serialize_null option
        $disabledNullType = $container->get('fos_elastica.index.index.type_serialize_null_disabled');
        $documentData = $disabledNullType->getDocument(1)->getData();
        $this->assertArrayNotHasKey('field1', $documentData);

        // Tests that attributes with null values are persisted into an Elasticsearch type with the serialize_null option
        $enabledNullType = $container->get('fos_elastica.index.index.type_serialize_null_enabled');
        $documentData = $enabledNullType->getDocument(1)->getData();
        $this->assertArrayHasKey('field1', $documentData);
        $this->assertEquals($documentData['field1'], null);
    }

    public function testUnmappedType()
    {
        $client = $this->createClient(array('test_case' => 'Serializer'));
        $resetter = $client->getContainer()->get('fos_elastica.resetter');
        $resetter->resetIndex('index');
    }

    public function testIfEventOnPostTransformEventIsCalled()
    {
        $client = $this->createClient(array('test_case' => 'SerializerWithListener'));
        $container = $client->getContainer();
        $object = new TypeObj();
        $object->id = 2;
        $object->field1 = 'a listener will change me';

        $elasticaPersister = $container->get('fos_elastica.object_persister.index.type');
        $elasticaPersister->insertOne($object);

        $elasticaType = $container->get('fos_elastica.index.index.type');

        $documentData = $elasticaType->getDocument(2)->getData();

        $this->assertArrayHasKey('field1', $documentData);
        $this->assertEquals($documentData['field1'], 'post_persister');
    }

    protected function setUp()
    {
        parent::setUp();

        $this->deleteTmpDir('Serializer');
    }

    protected function tearDown()
    {
        parent::tearDown();

        $this->deleteTmpDir('Serializer');
    }
}
