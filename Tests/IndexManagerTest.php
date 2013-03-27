<?php

namespace FOS\ElasticaBundle\Tests\IndexManager;

use FOS\ElasticaBundle\IndexManager;

class IndexManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FOS\ElasticaBundle\Tests\IndexManager
     */
    private $indexManager = null;

    public function setUp()
    {
        $this->indexManager = new IndexManager(array('index1' => 'test1', 'index2' => 'test2'), 'defaultIndex');
    }

    public function testThatWeCanGetAllIndexes()
    {
        $this->assertEquals(array('index1' => 'test1', 'index2' => 'test2'), $this->indexManager->getAllIndexes());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testThatWeCannotGetIndexWhichWasNotSet()
    {
        $this->indexManager->getIndex('index8');
    }
    
    public function testThatWeCanGetDefaultIndex()
    {
        $this->assertEquals('defaultIndex', $this->indexManager->getIndex(false));
        $this->assertEquals('defaultIndex', $this->indexManager->getDefaultIndex());
    }
    
    public function testThatWeCanGetIndex()
    {
        $this->assertEquals('test2', $this->indexManager->getIndex('index2'));
    }
}
