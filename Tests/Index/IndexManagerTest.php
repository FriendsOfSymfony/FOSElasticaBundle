<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Tests\Index;

use FOS\ElasticaBundle\Index\IndexManager;

class IndexManagerTest extends \PHPUnit_Framework_TestCase
{
    private $indexes = array();

    /**
     * @var IndexManager
     */
    private $indexManager;

    public function setUp()
    {
        foreach (array('index1', 'index2', 'index3') as $indexName) {
            $index = $this->getMockBuilder('FOS\\ElasticaBundle\\Elastica\\Index')
                ->disableOriginalConstructor()
                ->getMock();

            $index->expects($this->any())
                ->method('getName')
                ->will($this->returnValue($indexName));

            $this->indexes[$indexName] = $index;
        }

        $this->indexManager = new IndexManager($this->indexes, $this->indexes['index2']);
    }

    public function testGetAllIndexes()
    {
        $this->assertSame($this->indexes, $this->indexManager->getAllIndexes());
    }

    public function testGetIndex()
    {
        $this->assertSame($this->indexes['index1'], $this->indexManager->getIndex('index1'));
        $this->assertSame($this->indexes['index2'], $this->indexManager->getIndex('index2'));
        $this->assertSame($this->indexes['index3'], $this->indexManager->getIndex('index3'));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetIndexShouldThrowExceptionForInvalidName()
    {
        $this->indexManager->getIndex('index4');
    }

    public function testGetDefaultIndex()
    {
        $this->assertSame('index2', $this->indexManager->getIndex()->getName());
        $this->assertSame('index2', $this->indexManager->getDefaultIndex()->getName());
    }
}
