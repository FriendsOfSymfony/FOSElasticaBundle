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

use FOS\ElasticaBundle\Event\PostIndexPopulateEvent;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class PostIndexPopulateEventTest extends TestCase
{
    public function testPopulate(): void
    {
        $event = new PostIndexPopulateEvent('index', false, []);

        $this->assertSame('index', $event->getIndex());
        $this->assertFalse($event->isReset());
        $this->assertSame([], $event->getOptions());
    }

    public function testPopulateReset(): void
    {
        $event = new PostIndexPopulateEvent('index', true, []);

        $this->assertTrue($event->isReset());
    }

    public function testPopulateOptions(): void
    {
        $event = new PostIndexPopulateEvent('index', false, [
            'option' => 'value',
        ]);

        $this->assertSame(['option' => 'value'], $event->getOptions());
        $this->assertSame('value', $event->getOption('option'));
    }

    public function testPopulateInvalidOption(): void
    {
        $event = new PostIndexPopulateEvent('index', false, []);

        $this->expectException(\InvalidArgumentException::class);
        $event->getOption('invalid_option');
    }
}
