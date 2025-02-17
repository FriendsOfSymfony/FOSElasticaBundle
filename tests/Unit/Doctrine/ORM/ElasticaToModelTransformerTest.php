<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <https://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Tests\Unit\Doctrine\ORM;

use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;
use FOS\ElasticaBundle\Doctrine\ORM\ElasticaToModelTransformer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class ElasticaToModelTransformerTest extends TestCase
{
    public const OBJECT_CLASS = \stdClass::class;

    /**
     * @var ManagerRegistry&MockObject
     */
    protected $registry;

    /**
     * @var ObjectManager&MockObject
     */
    protected $manager;

    /**
     * @var ObjectRepository&MockObject
     */
    protected $repository;

    protected function setUp(): void
    {
        $this->registry = $this->createMock(ManagerRegistry::class);
        $this->manager = $this->createMock(ObjectManager::class);

        $this->registry->expects($this->any())
            ->method('getManagerForClass')
            ->with(self::OBJECT_CLASS)
            ->will($this->returnValue($this->manager))
        ;

        $this->repository = $this
            ->getMockBuilder(ObjectRepository::class)
            ->setMethods([
                'customQueryBuilderCreator',
                'createQueryBuilder',
                'find',
                'findAll',
                'findBy',
                'findOneBy',
                'getClassName',
            ])->getMock()
        ;

        $this->manager->expects($this->any())
            ->method('getRepository')
            ->with(self::OBJECT_CLASS)
            ->will($this->returnValue($this->repository))
        ;
    }

    /**
     * Tests that the Transformer uses the query_builder_method configuration option
     * allowing configuration of createQueryBuilder call.
     */
    public function testTransformUsesQueryBuilderMethodConfiguration()
    {
        $qb = $this->createMock(QueryBuilder::class);

        $this->repository->expects($this->once())
            ->method('customQueryBuilderCreator')
            ->with($this->equalTo(ElasticaToModelTransformer::ENTITY_ALIAS))
            ->will($this->returnValue($qb))
        ;
        $this->repository->expects($this->never())
            ->method('createQueryBuilder')
        ;

        $transformer = new ElasticaToModelTransformer($this->registry, self::OBJECT_CLASS, [
            'query_builder_method' => 'customQueryBuilderCreator',
        ]);

        $class = new \ReflectionClass(ElasticaToModelTransformer::class);
        $method = $class->getMethod('getEntityQueryBuilder');
        $method->setAccessible(true);

        $method->invokeArgs($transformer, []);
    }

    /**
     * Tests that the Transformer uses the query_builder_method configuration option
     * allowing configuration of createQueryBuilder call.
     */
    public function testTransformUsesDefaultQueryBuilderMethodConfiguration()
    {
        $qb = $this->createMock(QueryBuilder::class);

        $this->repository->expects($this->never())
            ->method('customQueryBuilderCreator')
        ;
        $this->repository->expects($this->once())
            ->method('createQueryBuilder')
            ->with($this->equalTo(ElasticaToModelTransformer::ENTITY_ALIAS))
            ->will($this->returnValue($qb))
        ;

        $transformer = new ElasticaToModelTransformer($this->registry, self::OBJECT_CLASS);

        $class = new \ReflectionClass(ElasticaToModelTransformer::class);
        $method = $class->getMethod('getEntityQueryBuilder');
        $method->setAccessible(true);

        $method->invokeArgs($transformer, []);
    }

    /**
     * Checks that the 'hints' parameter is used on the created query.
     */
    public function testUsesHintsConfigurationIfGiven()
    {
        $query = $this->getMockBuilder(Query::class)
            ->setMethods(['setHint', 'execute', 'setHydrationMode'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass()
        ;
        $query->expects($this->any())->method('setHydrationMode')->willReturnSelf();
        $query->expects($this->once())  //  check if the hint is set
            ->method('setHint')
            ->with('customHintName', 'Custom\Hint\Class')
            ->willReturnSelf()
        ;

        $qb = $this->createMock(QueryBuilder::class);
        $qb->expects($this->any())->method('getQuery')->willReturn($query);
        $qb->expects($this->any())->method('expr')->willReturn($this->createMock(Expr::class));
        $qb->expects($this->any())->method('andWhere')->willReturnSelf();

        $this->repository->expects($this->once())
            ->method('createQueryBuilder')
            ->with($this->equalTo(ElasticaToModelTransformer::ENTITY_ALIAS))
            ->will($this->returnValue($qb))
        ;

        $transformer = new ElasticaToModelTransformer($this->registry, self::OBJECT_CLASS, [
            'hints' => [
                ['name' => 'customHintName', 'value' => 'Custom\Hint\Class'],
            ],
        ]);

        $class = new \ReflectionClass(ElasticaToModelTransformer::class);
        $method = $class->getMethod('findByIdentifiers');
        $method->setAccessible(true);

        $method->invokeArgs($transformer, [[1, 2, 3], /* $hydrate */ true]);
    }
}
