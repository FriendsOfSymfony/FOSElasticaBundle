<?php

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

        $this->repository = $this->getMock('Doctrine\Common\Persistence\ObjectRepository', array(
            'customQueryBuilderCreator',
            'createQueryBuilder',
            'find',
            'findAll',
            'findBy',
            'findOneBy',
            'getClassName',
        ));

        $this->manager->expects($this->any())
            ->method('getRepository')
            ->with($this->objectClass)
            ->will($this->returnValue($this->repository));
    }
}
