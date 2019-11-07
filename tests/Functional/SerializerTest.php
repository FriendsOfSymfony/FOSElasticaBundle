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
class SerializerTest extends WebTestCase
{
    public function testMappingIteratorToArrayField()
    {
        static::bootKernel(['test_case' => 'Serializer']);
        $persister = static::$kernel->getContainer()->get('fos_elastica.object_persister.index');

        $object = new TypeObj();
        $object->id = 1;
        $object->coll = new \ArrayIterator(['foo', 'bar']);
        $persister->insertOne($object);

        $object->coll = new \ArrayIterator(['foo', 'bar', 'bazz']);
        $object->coll->offsetUnset(1);

        $persister->replaceOne($object);
    }

    /**
     * Tests that the serialize_null configuration attribute works.
     */
    public function testWithNullValues()
    {
        static::bootKernel(['test_case' => 'Serializer']);
        $container = static::$kernel->getContainer();

        $disabledNullPersister = $container->get('fos_elastica.object_persister.index_serialize_null_disabled');
        $enabledNullPersister = $container->get('fos_elastica.object_persister.index_serialize_null_enabled');

        $object = new TypeObj();
        $object->id = 1;
        $object->field1 = null;
        $disabledNullPersister->insertOne($object);
        $enabledNullPersister->insertOne($object);

        // Tests that attributes with null values are not persisted into an Elasticsearch type without the serialize_null option
        $disabledNullType = $container->get('fos_elastica.index.index_serialize_null_disabled');
        $documentData = $disabledNullType->getDocument(1)->getData();
        $this->assertArrayNotHasKey('field1', $documentData);

        // Tests that attributes with null values are persisted into an Elasticsearch type with the serialize_null option
        $enabledNullType = $container->get('fos_elastica.index.index_serialize_null_enabled');
        $documentData = $enabledNullType->getDocument(1)->getData();
        $this->assertArrayHasKey('field1', $documentData);
        $this->assertNull($documentData['field1']);
    }

    public function testUnmappedType()
    {
        static::bootKernel(['test_case' => 'Serializer']);
        $resetter = static::$kernel->getContainer()->get('fos_elastica.resetter');
        $resetter->resetIndex('index');
    }
}
