<?php

namespace FOQ\ElasticaBundle\Tests\IndexManager;

use FOQ\ElasticaBundle\IndexManager;

class IndexManagerTest extends \PHPUnit_Framework_TestCase
{
    private $defaultIndexName;
    private $indexesByName;
    private $indexManager;

    public function setUp()
    {
        $this->defaultIndexName = 'index2';
        $this->indexesByName = array(
            'index1' => 'test1',
            'index2' => 'test2',
        );
        $this->indexManager = new IndexManager($this->indexesByName, $this->defaultIndexName);
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
     * @expectedException InvalidArgumentException
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
