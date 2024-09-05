<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Tests\Unit\Index;

use FOS\ElasticaBundle\Elastica\Index;
use FOS\ElasticaBundle\Index\IndexManager;
use PHPUnit\Framework\TestCase;

class IndexManagerTest extends TestCase
{
    private $indexes = [];

    /**
     * @var IndexManager
     */
    private $indexManager;

    protected function setUp(): void
    {
        foreach (['index1', 'index2', 'index3'] as $indexName) {
            $index = $this->createMock(Index::class);

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

    public function testGetIndexShouldThrowExceptionForInvalidName()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->indexManager->getIndex('index4');
    }

    public function testGetDefaultIndex()
    {
        $this->assertSame('index2', $this->indexManager->getIndex()->getName());
        $this->assertSame('index2', $this->indexManager->getDefaultIndex()->getName());
    }
}
