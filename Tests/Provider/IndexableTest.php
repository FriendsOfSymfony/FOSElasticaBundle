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

class IndexableTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provideIsIndexableCallbacks
     */
    public function testValidIndexableCallbacks($callback, $return)
    {
        $indexable = new Indexable(array(
            'index/type' => $callback
        ));
        $index = $indexable->isObjectIndexable('index', 'type', new Entity);

        $this->assertEquals($return, $index);
    }

    /**
     * @dataProvider provideInvalidIsIndexableCallbacks
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidIsIndexableCallbacks($callback)
    {
        $indexable = new Indexable(array(
            'index/type' => $callback
        ));
        $indexable->isObjectIndexable('index', 'type', new Entity);
    }

    public function provideInvalidIsIndexableCallbacks()
    {
        return array(
            array('nonexistentEntityMethod'),
            array(array(new IndexableDecider(), 'internalMethod')),
            array(42),
            array('entity.getIsIndexable() && nonexistentEntityFunction()'),
        );
    }

    public function provideIsIndexableCallbacks()
    {
        return array(
            array('isIndexable', false),
            array(array(new IndexableDecider(), 'isIndexable'), true),
            array(function(Entity $entity) { return $entity->maybeIndex(); }, true),
            array('entity.maybeIndex()', true),
            array('!object.isIndexable() && entity.property == "abc"', true),
            array('entity.property != "abc"', false),
        );
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
}
