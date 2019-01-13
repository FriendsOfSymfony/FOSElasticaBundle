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

use FOS\ElasticaBundle\Event\IndexResetEvent;
use PHPUnit\Framework\TestCase;

class IndexResetEventTest extends TestCase
{
    public function testForce()
    {
        $event = new IndexResetEvent('index', false, true);
        $this->assertTrue($event->isForce());

        $event = new IndexResetEvent('index', false, false);
        $this->assertFalse($event->isForce());
    }

    public function testPopulating()
    {
        $event = new IndexResetEvent('index', true, false);
        $this->assertTrue($event->isPopulating());

        $event = new IndexResetEvent('index', false, false);
        $this->assertFalse($event->isPopulating());
    }
}
