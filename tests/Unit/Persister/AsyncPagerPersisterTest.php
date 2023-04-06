<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <https://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Tests\Unit\Persister;

use FOS\ElasticaBundle\Message\AsyncPersistPage;
use FOS\ElasticaBundle\Persister\AsyncPagerPersister;
use FOS\ElasticaBundle\Persister\InPlacePagerPersister;
use FOS\ElasticaBundle\Persister\ObjectPersisterInterface;
use FOS\ElasticaBundle\Persister\PagerPersisterInterface;
use FOS\ElasticaBundle\Persister\PagerPersisterRegistry;
use FOS\ElasticaBundle\Persister\PersisterRegistry;
use FOS\ElasticaBundle\Provider\PagerInterface;
use FOS\ElasticaBundle\Provider\PagerProviderInterface;
use FOS\ElasticaBundle\Provider\PagerProviderRegistry;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @internal
 */
class AsyncPagerPersisterTest extends TestCase
{
    public function testShouldImplementPagerPersisterInterface()
    {
        $reflectionClass = new \ReflectionClass(AsyncPagerPersister::class);
        $this->assertTrue($reflectionClass->implementsInterface(PagerPersisterInterface::class));
    }

    public function testInsertDispatchAsyncPersistPageObject()
    {
        $pagerPersisterRegistry = new PagerPersisterRegistry($this->createMock(ServiceLocator::class));
        $pagerProviderRegistry = $this->createMock(PagerProviderRegistry::class);
        $messageBus = $this->createMock(MessageBusInterface::class);
        $sut = new AsyncPagerPersister($pagerPersisterRegistry, $pagerProviderRegistry, $messageBus);

        $messageBus->expects($this->once())->method('dispatch')->with(
            $this->callback(
                function ($message) {
                    return $message instanceof AsyncPersistPage;
                }
            )
        )->willReturn(new Envelope(new AsyncPersistPage(0, [])));

        $pager = $this->createMock(PagerInterface::class);
        $sut->insert($pager);
    }
}
