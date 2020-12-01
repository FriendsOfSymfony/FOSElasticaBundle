<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <https://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Tests\Unit\Event;

use FOS\ElasticaBundle\Event\PostIndexResetEvent;
use PHPUnit\Framework\TestCase;

class PostIndexResetEventTest extends TestCase
{
    public function testReset()
    {
        $event = new PostIndexResetEvent('index', false, false);

        $this->assertSame('index', $event->getIndex());
        $this->assertFalse($event->isPopulating());
        $this->assertFalse($event->isForce());
    }

    public function testPopulatingReset()
    {
        $event = new PostIndexResetEvent('index', true, false);

        $this->assertTrue($event->isPopulating());
    }

    public function testForceReset()
    {
        $event = new PostIndexResetEvent('index', false, true);

        $this->assertTrue($event->isForce());
    }
}
