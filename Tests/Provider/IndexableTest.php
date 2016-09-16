<?php

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
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;

class IndexableTest extends \PHPUnit_Framework_TestCase
{
    public $container;

    public function testIndexableUnknown()
    {
        $indexable = new Indexable(array(), $this->container);
        $index = $indexable->isObjectIndexable('index', 'type', new Entity());

        $this->assertTrue($index);
    }

    /**
     * @dataProvider provideIsIndexableCallbacks
     */
    public function testValidIndexableCallbacks($callback, $return)
    {
        $indexable = new Indexable(array(
            'index/type' => $callback,
        ), $this->container);
        $index = $indexable->isObjectIndexable('index', 'type', new Entity());

        $this->assertEquals($return, $index);
    }

    /**
     * @dataProvider provideInvalidIsIndexableCallbacks
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidIsIndexableCallbacks($callback)
    {
        $indexable = new Indexable(array(
            'index/type' => $callback,
        ), $this->container);
        $indexable->isObjectIndexable('index', 'type', new Entity());
    }

    public function provideInvalidIsIndexableCallbacks()
    {
        return array(
            array('nonexistentEntityMethod'),
            array(array('@indexableService', 'internalMethod')),
            array(array(new IndexableDecider(), 'internalMethod')),
            array(42),
            array('entity.getIsIndexable() && nonexistentEntityFunction()'),
        );
    }

    public function testObjectIsNotIndexableIfIndexingDisabled()
    {
        $indexable = new Indexable(array(), $this->container);
        $indexable->setIndexingEnabled(false);
        $index = $indexable->isObjectIndexable('index', 'type', new Entity());

        $this->assertFalse($index);
    }

    public function provideIsIndexableCallbacks()
    {
        return array(
            array('isIndexable', false),
            array(array(new IndexableDecider(), 'isIndexable'), true),
            array(array('@indexableService', 'isIndexable'), true),
            array(array('@indexableService'), true),
            array(function (Entity $entity) { return $entity->maybeIndex(); }, true),
            array('entity.maybeIndex()', true),
            array('!object.isIndexable() && entity.property == "abc"', true),
            array('entity.property != "abc"', false),
            array('["array", "values"]', true),
            array('[]', false)
        );
    }

    protected function setUp()
    {
        $this->container = $this->getMockBuilder('Symfony\\Component\\DependencyInjection\\ContainerInterface')
            ->getMock();

        $this->container->expects($this->any())
            ->method('get')
            ->with('indexableService')
            ->will($this->returnValue(new IndexableDecider()));
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
    public function isIndexable(Entity $entity)
    {
        return !$entity->isIndexable();
    }

    protected function internalMethod()
    {
    }

    public function __invoke($object)
    {
        return true;
    }
}
