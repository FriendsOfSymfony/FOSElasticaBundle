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
 * (c) Infinite Networks Pty Ltd <http://www.infinite.net.au>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Tests\Provider;

use FOS\ElasticaBundle\Provider\Indexable;

class IndexableTest extends \PHPUnit_Framework_TestCase
{
    public $container;

    protected function setUp()
    {
        $this->container = $this->getMockBuilder('Symfony\\Component\\DependencyInjection\\ContainerInterface')
            ->getMock();

        $this->container->expects($this->any())
            ->method('get')
            ->with('indexableService')
            ->will($this->returnValue(new IndexableDecider()));
    }

    public function testIndexableUnknown()
    {
        $indexable = new Indexable([]);
        $indexable->setContainer($this->container);
        $index = $indexable->isObjectIndexable('index', 'type', new Entity());

        $this->assertTrue($index);
    }

    /**
     * @dataProvider provideIsIndexableCallbacks
     */
    public function testValidIndexableCallbacks($callback, $return)
    {
        $indexable = new Indexable([
            'index/type' => $callback,
        ]);
        $indexable->setContainer($this->container);
        $index = $indexable->isObjectIndexable('index', 'type', new Entity());

        $this->assertSame($return, $index);
    }

    /**
     * @dataProvider provideInvalidIsIndexableCallbacks
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidIsIndexableCallbacks($callback)
    {
        $indexable = new Indexable([
            'index/type' => $callback,
        ]);
        $indexable->setContainer($this->container);
        $indexable->isObjectIndexable('index', 'type', new Entity());
    }

    public function provideInvalidIsIndexableCallbacks()
    {
        return [
            ['nonexistentEntityMethod'],
            [['@indexableService', 'internalMethod']],
            [[new IndexableDecider(), 'internalMethod']],
            [42],
            ['entity.getIsIndexable() && nonexistentEntityFunction()'],
        ];
    }

    public function provideIsIndexableCallbacks()
    {
        return [
            ['isIndexable', false],
            [[new IndexableDecider(), 'isIndexable'], true],
            [['@indexableService', 'isIndexable'], true],
            [['@indexableService'], true],
            [function (Entity $entity) {
                return $entity->maybeIndex();
            }, true],
            ['entity.maybeIndex()', true],
            ['!object.isIndexable() && entity.property == "abc"', true],
            ['entity.property != "abc"', false],
            ['["array", "values"]', true],
            ['[]', false],
        ];
    }
}

class Entity
{
    public $property = 'abc';

    public function isIndexable()
    {
        return false;
    }

    public function maybeIndex()
    {
        return true;
    }
}

class IndexableDecider
{
    public function __invoke($object)
    {
        return true;
    }

    public function isIndexable(Entity $entity)
    {
        return !$entity->isIndexable();
    }

    protected function internalMethod()
    {
    }
}
