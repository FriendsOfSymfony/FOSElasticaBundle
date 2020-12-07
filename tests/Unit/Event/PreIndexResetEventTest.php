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

use FOS\ElasticaBundle\Event\PreIndexResetEvent;
use PHPUnit\Framework\TestCase;

class PreIndexResetEventTest extends TestCase
{
    public function testReset()
    {
        $event = new PreIndexResetEvent('index', false, false);

        $this->assertSame('index', $event->getIndex());
        $this->assertFalse($event->isPopulating());
        $this->assertFalse($event->isForce());
    }

    public function testPopulatingReset()
    {
        $event = new PreIndexResetEvent('index', true, false);

        $this->assertTrue($event->isPopulating());
    }

    public function testForceReset()
    {
        $event = new PreIndexResetEvent('index', false, true);
        $this->assertTrue($event->isForce());

        $event->setForce(false);
        $this->assertFalse($event->isForce());
    }
}
