<?php

namespace FOS\ElasticaBundle\Tests\IndexManager;

use FOS\ElasticaBundle\IndexManager;

class IndexManagerTest extends \PHPUnit_Framework_TestCase
{
    private $defaultIndexName;
    private $indexesByName;
    /** @var IndexManager */
    private $indexManager;

    public function setUp()
    {
        $this->defaultIndexName = 'index2';
        $this->indexesByName = array(
            'index1' => 'test1',
            'index2' => 'test2',
        );

        /** @var $defaultIndex \PHPUnit_Framework_MockObject_MockObject|\Elastica\Index */
        $defaultIndex = $this->getMockBuilder('Elastica\Index')
            ->disableOriginalConstructor()
            ->getMock();

        $defaultIndex->expects($this->any())
            ->method('getName')
            ->will($this->returnValue($this->defaultIndexName));

        $this->indexManager = new IndexManager($tbd, $defaultIndex);
    }

    public function testGetAllIndexes()
    {
        $this->assertEquals($this->indexesByName, $this->indexManager->getAllIndexes());
    }

    public function testGetIndex()
    {
        $this->assertEquals($this->indexesByName['index1'], $this->indexManager->getIndex('index1'));
        $this->assertEquals($this->indexesByName['index2'], $this->indexManager->getIndex('index2'));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetIndexShouldThrowExceptionForInvalidName()
    {
        $this->indexManager->getIndex('index3');
    }

    public function testGetDefaultIndex()
    {
        $this->assertEquals('test2', $this->indexManager->getIndex());
        $this->assertEquals('test2', $this->indexManager->getDefaultIndex());
    }
}
