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

use FOS\ElasticaBundle\Event\TypePopulateEvent;
use PHPUnit\Framework\TestCase;

class TypePopulateEventTest extends TestCase
{
    public function testType()
    {
        $event = new TypePopulateEvent('index', 'type', false, []);
        $this->assertEquals('type', $event->getType());
    }
}
