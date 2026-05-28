<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <https://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Tests\Unit\EventListener;

use FOS\ElasticaBundle\Event\PostIndexPopulateEvent;
use FOS\ElasticaBundle\EventListener\PopulateListener;
use FOS\ElasticaBundle\Index\Resetter;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class PopulateListenerTest extends TestCase
{
    public function testOnPostIndexPopulateWithReset(): void
    {
        $indexName = 'index';
        $deleteOption = true;

        $stub = $this->mockResetter(1, $indexName, $deleteOption);
        $listener = new PopulateListener($stub);

        $event = new PostIndexPopulateEvent($indexName, true, ['delete' => $deleteOption]);
        $listener->onPostIndexPopulate($event);
    }

    public function testOnPostIndexPopulateWithoutReset(): void
    {
        $indexName = 'index';
        $deleteOption = true;

        $stub = $this->mockResetter(0, $indexName, $deleteOption);
        $listener = new PopulateListener($stub);

        $event = new PostIndexPopulateEvent($indexName, false, ['delete' => $deleteOption]);
        $listener->onPostIndexPopulate($event);
    }

    private function mockResetter(int $numberOfCalls, string $indexName, bool $deleteOption): \PHPUnit\Framework\MockObject\MockObject
    {
        $stub = $this
            ->getMockBuilder(Resetter::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $stub
            ->expects($this->exactly($numberOfCalls))
            ->method('switchIndexAlias')
            ->with($indexName, $deleteOption)
        ;

        return $stub;
    }
}
