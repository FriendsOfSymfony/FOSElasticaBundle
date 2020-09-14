<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Persister;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

class PagerPersisterRegistryTest extends TestCase
{
    public function testShouldImplementContainerAwareInterface()
    {
        $rc = new \ReflectionClass(PagerPersisterRegistry::class);

        $this->assertTrue($rc->implementsInterface(ContainerAwareInterface::class));
    }

    public function testShouldBeFinal()
    {
        $rc = new \ReflectionClass(PagerPersisterRegistry::class);

        $this->assertTrue($rc->isFinal());
    }

    public function testCouldBeConstructedWithNameToServiceIdMap()
    {
        new PagerPersisterRegistry([]);
    }

    public function testThrowsIfThereIsNoSuchEntryInNameToServiceIdMap()
    {
        $container = new Container();

        $registry = new PagerPersisterRegistry([
            'the_name' => 'the_service_id',
        ]);
        $registry->setContainer($container);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('No pager persister was registered for the give name "the_other_name".');
        $registry->getPagerPersister('the_other_name');
    }

    public function testThrowsIfRelatedServiceDoesNotImplementPagerPersisterInterface()
    {
        $container = new Container();
        $container->set('the_service_id', new \stdClass());

        $registry = new PagerPersisterRegistry([
            'the_name' => 'the_service_id',
        ]);
        $registry->setContainer($container);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('The pager provider service "the_service_id" must implement "FOS\ElasticaBundle\Persister\PagerPersisterInterface" interface but it is an instance of "stdClass" class.');
        $registry->getPagerPersister('the_name');
    }

    public function testThrowsIfThereIsServiceWithSuchId()
    {
        $container = new Container();

        $registry = new PagerPersisterRegistry([
            'the_name' => 'the_service_id',
        ]);
        $registry->setContainer($container);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('You have requested a non-existent service "the_service_id".');
        $registry->getPagerPersister('the_name');
    }

    public function testShouldReturnPagerPersisterByGivenName()
    {
        $pagerPersisterMock = $this->createPagerPersisterMock();

        $container = new Container();
        $container->set('the_service_id', $pagerPersisterMock);

        $registry = new PagerPersisterRegistry([
            'the_name' => 'the_service_id',
        ]);
        $registry->setContainer($container);

        $actualPagerPersister = $registry->getPagerPersister('the_name');

        $this->assertSame($pagerPersisterMock, $actualPagerPersister);
    }

    /**
     * @return PagerPersisterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createPagerPersisterMock()
    {
        return $this->createMock(PagerPersisterInterface::class);
    }
}
