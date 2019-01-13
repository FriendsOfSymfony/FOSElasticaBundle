<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Tests\Unit\Event;

use FOS\ElasticaBundle\Event\IndexPopulateEvent;
use PHPUnit\Framework\TestCase;

class IndexPopulateEventTest extends TestCase
{
    /**
     * @var IndexPopulateEvent
     */
    private $event;

    protected function setUp()
    {
        $this->event = new IndexPopulateEvent('index', false, []);
    }

    public function testReset()
    {
        $this->assertFalse($this->event->isReset());
        $this->event->setReset(true);
        $this->assertTrue($this->event->isReset());
    }

    public function testOption()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->event->getOption('name');
        $this->event->setOption('name', 'value');
        $this->assertEquals('value', $this->event->getOption('name'));
    }
}
