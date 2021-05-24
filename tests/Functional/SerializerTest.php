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

/**
 * @group functional
 *
 * @internal
 */
class SerializerTest extends WebTestCase
{
    public function testMappingIteratorToArrayField()
    {
        static::bootKernel(['test_case' => 'Serializer']);
        $persister = static::$container->get('fos_elastica.object_persister.index');

        $object = new TypeObj();
        $object->id = 1;
        $object->coll = ['foo', 'bar'];
        $persister->insertOne($object);

        $object->coll = ['foo', 'bar', 'bazz'];
        unset($object->coll[1]);

        $persister->replaceOne($object);
    }

    /**
     * Tests that the serialize_null configuration attribute works.
     */
    public function testWithNullValues()
    {
        static::bootKernel(['test_case' => 'Serializer']);

        $disabledNullPersister = static::$container->get('fos_elastica.object_persister.index_serialize_null_disabled');
        $enabledNullPersister = static::$container->get('fos_elastica.object_persister.index_serialize_null_enabled');

        $object = new TypeObj();
        $object->id = 1;
        $object->field1 = null;
        $disabledNullPersister->insertOne($object);
        $enabledNullPersister->insertOne($object);

        // Tests that attributes with null values are not persisted into an Elasticsearch type without the serialize_null option
        $disabledNullType = static::$container->get('fos_elastica.index.index_serialize_null_disabled');
        $documentData = $disabledNullType->getDocument(1)->getData();
        $this->assertArrayNotHasKey('field1', $documentData);

        // Tests that attributes with null values are persisted into an Elasticsearch type with the serialize_null option
        $enabledNullType = static::$container->get('fos_elastica.index.index_serialize_null_enabled');
        $documentData = $enabledNullType->getDocument(1)->getData();
        $this->assertArrayHasKey('field1', $documentData);
        $this->assertNull($documentData['field1']);
    }

    public function testUnmappedType()
    {
        static::bootKernel(['test_case' => 'Serializer']);
        $resetter = static::$container->get('fos_elastica.resetter');
        $resetter->resetIndex('index');
    }
}
