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

use Elastica\Index;
use FOS\ElasticaBundle\Index\ResetterInterface;
use FOS\ElasticaBundle\Persister\ObjectPersisterInterface;

/**
 * @group functional
 */
class MappingToElasticaTest extends WebTestCase
{
    public function testResetIndexAddsMappings()
    {
        self::bootKernel(['test_case' => 'Basic']);
        $resetter = $this->getResetter();
        $resetter->resetIndex('index');

        $index = $this->getIndex();
        $mapping = $index->getMapping();

        $this->assertNotEmpty($mapping, 'Mapping was populated');

        $index = $this->getIndex();
        $mapping = $index->getMapping();

        $this->assertSame('strict', $mapping['dynamic']);
        $this->assertFalse($mapping['date_detection']);
        $this->assertTrue($mapping['numeric_detection']);
        $this->assertSame(['yyyy-MM-dd'], $mapping['dynamic_date_formats']);
        $this->assertArrayHasKey('dynamic', $mapping['properties']['dynamic_allowed']);
        $this->assertSame('true', $mapping['properties']['dynamic_allowed']['dynamic']);
    }

    public function testORMResetIndexAddsMappings()
    {
        self::bootKernel(['test_case' => 'ORM']);
        $resetter = $this->getResetter();
        $resetter->resetIndex('index');

        $index = $this->getIndex();
        $mapping = $index->getMapping();

        $this->assertNotEmpty($mapping, 'Mapping was populated');
    }

    public function testMappingIteratorToArrayField()
    {
        self::bootKernel(['test_case' => 'ORM']);
        /** @var ObjectPersisterInterface $persister */
        $persister = self::$container->get('fos_elastica.object_persister.index');

        $object = new TypeObj();
        $object->id = 1;
        $object->coll = new \ArrayIterator(['foo', 'bar']);
        $persister->insertOne($object);

        $object->coll = new \ArrayIterator(['foo', 'bar', 'bazz']);
        $object->coll->offsetUnset(1);

        $persister->replaceOne($object);
    }

    private function getResetter(): ResetterInterface
    {
        return self::$container->get('fos_elastica.resetter');
    }

    private function getIndex(string $name = 'index'): Index
    {
        return self::$container->get('fos_elastica.index.'.$name);
    }
}
