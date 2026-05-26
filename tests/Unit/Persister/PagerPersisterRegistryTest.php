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

use FOS\ElasticaBundle\Persister\PagerPersisterInterface;
use FOS\ElasticaBundle\Persister\PagerPersisterRegistry;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ServiceLocator;

/**
 * @internal
 */
class PagerPersisterRegistryTest extends TestCase
{
    public function testShouldBeFinal(): void
    {
        $rc = new \ReflectionClass(PagerPersisterRegistry::class);

        $this->assertTrue($rc->isFinal());
    }

    public function testThrowsIfThereIsNoSuchEntryInNameToServiceIdMap(): void
    {
        $serviceLocator = $this->createMock(ServiceLocator::class);
        $serviceLocator->expects($this->once())->method('has')->with('the_name')->willReturn(false);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('No pager persister was registered for the give name "the_name".');

        (new PagerPersisterRegistry($serviceLocator))->getPagerPersister('the_name');
    }

    public function testThrowsIfRelatedServiceDoesNotImplementPagerPersisterInterface(): void
    {
        $serviceLocator = $this->createMock(ServiceLocator::class);
        $serviceLocator->expects($this->once())->method('has')->with('the_name')->willReturn(true);
        $serviceLocator->expects($this->once())->method('get')->with('the_name')->willReturn(new \stdClass());

        $this->expectException(\TypeError::class);

        $this->expectExceptionMessage('FOS\ElasticaBundle\Persister\PagerPersisterRegistry::getPagerPersister(): Return value must be of type FOS\ElasticaBundle\Persister\PagerPersisterInterface, stdClass returned');

        (new PagerPersisterRegistry($serviceLocator))->getPagerPersister('the_name');
    }

    public function testShouldReturnPagerPersisterByGivenName(): void
    {
        $pagerPersisterMock = $this->createPagerPersisterMock();

        $serviceLocator = $this->createMock(ServiceLocator::class);
        $serviceLocator->expects($this->once())->method('has')->with('the_name')->willReturn(true);
        $serviceLocator->expects($this->once())->method('get')->with('the_name')->willReturn($pagerPersisterMock);

        $registry = new PagerPersisterRegistry($serviceLocator);

        $actualPagerPersister = $registry->getPagerPersister('the_name');

        $this->assertSame($pagerPersisterMock, $actualPagerPersister);
    }

    /**
     * @return PagerPersisterInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private function createPagerPersisterMock(): \PHPUnit\Framework\MockObject\MockObject
    {
        return $this->createMock(PagerPersisterInterface::class);
    }
}
