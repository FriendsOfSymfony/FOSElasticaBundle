<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Tests\Doctrine\ORM;

use FOS\ElasticaBundle\Doctrine\ORM\ElasticaToModelTransformer;

class ElasticaToModelTransformerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Doctrine\Common\Persistence\ManagerRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $registry;

    /**
     * @var \Doctrine\ORM\EntityManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $manager;

    /**
     * @var \Doctrine\Common\Persistence\ObjectRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $repository;

    /**
     * @var string
     */
    protected $objectClass = 'stdClass';

    /**
     * Tests that the Transformer uses the query_builder_method configuration option
     * allowing configuration of createQueryBuilder call.
     */
    public function testTransformUsesQueryBuilderMethodConfiguration()
    {
        $qb = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $this->repository->expects($this->once())
            ->method('customQueryBuilderCreator')
            ->with($this->equalTo(ElasticaToModelTransformer::ENTITY_ALIAS))
            ->will($this->returnValue($qb));
        $this->repository->expects($this->never())
            ->method('createQueryBuilder');

        $transformer = new ElasticaToModelTransformer($this->registry, $this->objectClass, array(
            'query_builder_method' => 'customQueryBuilderCreator',
        ));

        $class = new \ReflectionClass('FOS\ElasticaBundle\Doctrine\ORM\ElasticaToModelTransformer');
        $method = $class->getMethod('getEntityQueryBuilder');
        $method->setAccessible(true);

        $method->invokeArgs($transformer, array());
    }

    /**
     * Tests that the Transformer uses the query_builder_method configuration option
     * allowing configuration of createQueryBuilder call.
     */
    public function testTransformUsesDefaultQueryBuilderMethodConfiguration()
    {
        $qb = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $this->repository->expects($this->never())
            ->method('customQueryBuilderCreator');
        $this->repository->expects($this->once())
            ->method('createQueryBuilder')
            ->with($this->equalTo(ElasticaToModelTransformer::ENTITY_ALIAS))
            ->will($this->returnValue($qb));

        $transformer = new ElasticaToModelTransformer($this->registry, $this->objectClass);

        $class = new \ReflectionClass('FOS\ElasticaBundle\Doctrine\ORM\ElasticaToModelTransformer');
        $method = $class->getMethod('getEntityQueryBuilder');
        $method->setAccessible(true);

        $method->invokeArgs($transformer, array());
    }

    /**
     * Checks that the 'hints' parameter is used on the created query.
     */
    public function testUsesHintsConfigurationIfGiven()
    {
        $query = $this->getMockBuilder('Doctrine\ORM\AbstractQuery')
            ->setMethods(array('setHint', 'execute', 'setHydrationMode'))
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $query->expects($this->any())->method('setHydrationMode')->willReturnSelf();
        $query->expects($this->once())  //  check if the hint is set
            ->method('setHint')
            ->with('customHintName', 'Custom\Hint\Class')
            ->willReturnSelf();

        $qb = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $qb->expects($this->any())->method('getQuery')->willReturn($query);
        $qb->expects($this->any())->method('expr')->willReturn($this->getMockBuilder('Doctrine\ORM\Query\Expr')->getMock());
        $qb->expects($this->any())->method('andWhere')->willReturnSelf();

        $this->repository->expects($this->once())
            ->method('createQueryBuilder')
            ->with($this->equalTo(ElasticaToModelTransformer::ENTITY_ALIAS))
            ->will($this->returnValue($qb));

        $transformer = new ElasticaToModelTransformer($this->registry, $this->objectClass, array(
            'hints' => array(
                array('name' => 'customHintName', 'value' => 'Custom\Hint\Class'),
            ),
        ));

        $class = new \ReflectionClass('FOS\ElasticaBundle\Doctrine\ORM\ElasticaToModelTransformer');
        $method = $class->getMethod('findByIdentifiers');
        $method->setAccessible(true);

        $method->invokeArgs($transformer, array(array(1, 2, 3), /* $hydrate */true));
    }

    protected function setUp()
    {
        $this->registry = $this->getMockBuilder('Doctrine\Common\Persistence\ManagerRegistry')
            ->disableOriginalConstructor()
            ->getMock();

        $this->manager = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->registry->expects($this->any())
            ->method('getManagerForClass')
            ->with($this->objectClass)
            ->will($this->returnValue($this->manager));

        $this->repository = $this
            ->getMockBuilder('Doctrine\Common\Persistence\ObjectRepository')
            ->setMethods(array(
                'customQueryBuilderCreator',
                'createQueryBuilder',
                'find',
                'findAll',
                'findBy',
                'findOneBy',
                'getClassName',
            ))->getMock();

        $this->manager->expects($this->any())
            ->method('getRepository')
            ->with($this->objectClass)
            ->will($this->returnValue($this->repository));
    }
}
