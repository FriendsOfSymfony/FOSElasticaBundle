<?php

namespace FOS\ElasticaBundle\Tests\Doctrine;

use Elastica\Result;
use FOS\ElasticaBundle\Doctrine\ORM\ElasticaToModelTransformer;
use Symfony\Component\PropertyAccess\PropertyAccess;

class AbstractElasticaToModelTransformerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Doctrine\Common\Persistence\ManagerRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $registry;

    /**
     * @var string
     */
    protected $objectClass = 'stdClass';

    /**
     * Tests if ignore_missing option is properly handled in transformHybrid() method
     */
    public function testIgnoreMissingOptionDuringTransformHybrid()
    {
        $transformer = $this->getMock(
            'FOS\ElasticaBundle\Doctrine\ORM\ElasticaToModelTransformer',
            array('findByIdentifiers'),
            array($this->registry, $this->objectClass, array('ignore_missing' => true))
        );

        $transformer->setPropertyAccessor(PropertyAccess::createPropertyAccessor());

        $firstOrmResult = new \stdClass();
        $firstOrmResult->id = 1;
        $secondOrmResult = new \stdClass();
        $secondOrmResult->id = 3;
        $transformer->expects($this->once())
            ->method('findByIdentifiers')
            ->with(array(1, 2, 3))
            ->willReturn(array($firstOrmResult, $secondOrmResult));

        $firstElasticaResult = new Result(array('_id' => 1));
        $secondElasticaResult = new Result(array('_id' => 2));
        $thirdElasticaResult = new Result(array('_id' => 3));

        $hybridResults = $transformer->hybridTransform(array($firstElasticaResult, $secondElasticaResult, $thirdElasticaResult));

        $this->assertCount(2, $hybridResults);
        $this->assertEquals($firstOrmResult, $hybridResults[0]->getTransformed());
        $this->assertEquals($firstElasticaResult, $hybridResults[0]->getResult());
        $this->assertEquals($secondOrmResult, $hybridResults[1]->getTransformed());
        $this->assertEquals($thirdElasticaResult, $hybridResults[1]->getResult());
    }

    protected function setUp()
    {
        if (!interface_exists('Doctrine\Common\Persistence\ManagerRegistry')) {
            $this->markTestSkipped('Doctrine Common is not present');
        }

        $this->registry = $this->getMockBuilder('Doctrine\Common\Persistence\ManagerRegistry')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
