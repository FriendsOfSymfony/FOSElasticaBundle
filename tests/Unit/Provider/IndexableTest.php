<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Tests\Unit\Provider;

use FOS\ElasticaBundle\Provider\Indexable;
use PHPUnit\Framework\TestCase;

class IndexableTest extends TestCase
{
    public function testIndexableUnknown()
    {
        $indexable = new Indexable([]);
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
        $index = $indexable->isObjectIndexable('index', 'type', new Entity());

        $this->assertSame($return, $index);
    }

    /**
     * @dataProvider provideInvalidIsIndexableCallbacks
     */
    public function testInvalidIsIndexableCallbacks($callback)
    {
        $indexable = new Indexable([
            'index/type' => $callback,
        ]);

        $this->expectException(\InvalidArgumentException::class);

        $indexable->isObjectIndexable('index', 'type', new Entity());
    }

    public function provideInvalidIsIndexableCallbacks()
    {
        return [
            ['nonexistentEntityMethod'],
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
            [new IndexableDecider(), true],
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
