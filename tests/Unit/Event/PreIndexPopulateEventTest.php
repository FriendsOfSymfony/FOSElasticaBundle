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

use FOS\ElasticaBundle\Event\PreIndexPopulateEvent;
use PHPUnit\Framework\TestCase;

class PreIndexPopulateEventTest extends TestCase
{
    public function testPopulate()
    {
        $event = new PreIndexPopulateEvent('index', false, []);

        $this->assertSame('index', $event->getIndex());
        $this->assertFalse($event->isReset());
        $this->assertSame([], $event->getOptions());
    }

    public function testPopulateReset()
    {
        $event = new PreIndexPopulateEvent('index', false, []);
        $this->assertFalse($event->isReset());

        $event->setReset(true);
        $this->assertTrue($event->isReset());
    }

    public function testPopulateOptions()
    {
        $event = new PreIndexPopulateEvent('index', false, [
            'option_1' => 'value_1',
        ]);

        $this->assertSame(['option_1' => 'value_1'], $event->getOptions());
        $this->assertSame('value_1', $event->getOption('option_1'));

        $event->setOption('option_2', 'value_2');
        $this->assertSame(['option_1' => 'value_1', 'option_2' => 'value_2'], $event->getOptions());
        $this->assertSame('value_2', $event->getOption('option_2'));
    }

    public function testPopulateInvalidOption()
    {
        $event = new PreIndexPopulateEvent('index', false, []);

        $this->expectException(\InvalidArgumentException::class);
        $event->getOption('invalid_option');
    }
}
