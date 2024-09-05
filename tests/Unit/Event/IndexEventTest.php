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

use FOS\ElasticaBundle\Event\IndexEvent;
use PHPUnit\Framework\TestCase;

class IndexEventTest extends TestCase
{
    /**
     * @var IndexEvent
     */
    private $event;

    protected function setUp(): void
    {
        $this->event = new IndexEvent('index');
    }

    public function testIndex()
    {
        $this->assertEquals('index', $this->event->getIndex());
    }
}
