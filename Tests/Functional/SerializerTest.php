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

    public function testUnmappedType()
    {
        $client = $this->createClient(array('test_case' => 'Serializer'));
        $resetter = $client->getContainer()->get('fos_elastica.resetter');
        $resetter->resetIndex('index');
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
