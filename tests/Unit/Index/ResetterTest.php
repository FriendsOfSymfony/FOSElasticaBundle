<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <https://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Tests\Unit\Index;

use FOS\ElasticaBundle\Configuration\ConfigManager;
use FOS\ElasticaBundle\Configuration\IndexConfig;
use FOS\ElasticaBundle\Elastica\Index;
use FOS\ElasticaBundle\Event\PostIndexResetEvent;
use FOS\ElasticaBundle\Event\PreIndexResetEvent;
use FOS\ElasticaBundle\Index\AliasProcessor;
use FOS\ElasticaBundle\Index\IndexManager;
use FOS\ElasticaBundle\Index\MappingBuilder;
use FOS\ElasticaBundle\Index\Resetter;
use FOS\ElasticaBundle\Index\ResetterInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @internal
 */
class ResetterTest extends TestCase
{
    /**
     * @var Resetter
     */
    private $resetter;

    private $aliasProcessor;
    private $configManager;
    private $dispatcher;
    private $indexManager;
    private $mappingBuilder;

    protected function setUp(): void
    {
        $this->aliasProcessor = $this->createMock(AliasProcessor::class);
        $this->configManager = $this->createMock(ConfigManager::class);
        $this->dispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->indexManager = $this->createMock(IndexManager::class);
        $this->mappingBuilder = $this->createMock(MappingBuilder::class);

        $this->resetter = new Resetter(
            $this->configManager,
            $this->indexManager,
            $this->aliasProcessor,
            $this->mappingBuilder,
            $this->dispatcher
        );
    }

    public function testResetAllIndexes()
    {
        $indexName = 'index1';
        $indexConfig = new IndexConfig([
            'name' => $indexName,
            'config' => [],
            'mapping' => [],
            'model' => [],
        ]);
        $mapping = ['map' => 'ping'];
        $index = $this->mockIndex($indexName, $indexConfig, $mapping);

        $this->configManager->expects($this->once())
            ->method('getIndexNames')
            ->willReturn([$indexName])
        ;

        $this->dispatcherExpects([
            [$this->isInstanceOf(PreIndexResetEvent::class)],
            [$this->isInstanceOf(PostIndexResetEvent::class)],
        ]);

        $index->expects($this->once())
            ->method('create')
            ->with($mapping, ['recreate' => true])
        ;

        $this->resetter->resetAllIndexes();
    }

    public function testResetIndex()
    {
        $indexConfig = new IndexConfig([
            'name' => 'index1',
            'config' => [],
            'mapping' => [],
            'model' => [],
        ]);
        $mapping = ['map' => 'ping'];
        $index = $this->mockIndex('index1', $indexConfig, $mapping);

        $this->dispatcherExpects([
            [$this->isInstanceOf(PreIndexResetEvent::class)],
            [$this->isInstanceOf(PostIndexResetEvent::class)],
        ]);

        $index->expects($this->once())
            ->method('create')
            ->with($mapping, ['recreate' => true])
        ;

        $this->resetter->resetIndex('index1');
    }

    public function testResetIndexWithDifferentNameAndAlias()
    {
        $indexConfig = new IndexConfig([
            'name' => 'index1',
            'elasticSearchName' => 'notIndex1',
            'use_alias' => true,
            'config' => [],
            'mapping' => [],
            'model' => [],
        ]);
        $mapping = ['map' => 'ping'];
        $index = $this->mockIndex('index1', $indexConfig, $mapping);
        $this->dispatcherExpects([
            [$this->isInstanceOf(PreIndexResetEvent::class)],
            [$this->isInstanceOf(PostIndexResetEvent::class)],
        ]);

        $this->aliasProcessor->expects($this->once())
            ->method('switchIndexAlias')
            ->with($indexConfig, $index, false)
        ;

        $index->expects($this->once())
            ->method('create')
            ->with($mapping, ['recreate' => true])
        ;

        $this->resetter->resetIndex('index1');
    }

    public function testFailureWhenMissingIndexDoesntDispatch()
    {
        $this->configManager->expects($this->once())
            ->method('getIndexConfiguration')
            ->with('nonExistant')
            ->will($this->throwException(new \InvalidArgumentException()))
        ;

        $this->indexManager->expects($this->never())
            ->method('getIndex')
        ;

        $this->expectException(\InvalidArgumentException::class);
        $this->resetter->resetIndex('nonExistant');
    }

    public function testPostPopulateWithoutAlias()
    {
        $this->mockIndex('index', new IndexConfig([
            'name' => 'index',
            'config' => [],
            'mapping' => [],
            'model' => [],
        ]));

        $this->indexManager->expects($this->never())
            ->method('getIndex')
        ;
        $this->aliasProcessor->expects($this->never())
            ->method('switchIndexAlias')
        ;

        $this->resetter->switchIndexAlias('index');
    }

    public function testPostPopulate()
    {
        $indexConfig = new IndexConfig([
            'name' => 'index1',
            'use_alias' => true,
            'config' => [],
            'mapping' => [],
            'model' => [],
        ]);
        $index = $this->mockIndex('index', $indexConfig);

        $this->aliasProcessor->expects($this->once())
            ->method('switchIndexAlias')
            ->with($indexConfig, $index)
        ;

        $this->resetter->switchIndexAlias('index');
    }

    public function testResetterImplementsResetterInterface()
    {
        $this->assertInstanceOf(ResetterInterface::class, $this->resetter);
    }

    private function dispatcherExpects(array $events)
    {
        $expectation = $this->dispatcher->expects($this->exactly(\count($events)))
            ->method('dispatch')
        ;

        \call_user_func_array([$expectation, 'withConsecutive'], $events);
    }

    private function mockIndex(string $indexName, IndexConfig $config, array $mapping = []): Index&MockObject
    {
        $this->configManager->expects($this->atLeast(1))
            ->method('getIndexConfiguration')
            ->with($indexName)
            ->willReturn($config)
        ;
        $index = $this->createMock(Index::class);
        $this->indexManager->expects($this->any())
            ->method('getIndex')
            ->with($indexName)
            ->willReturn($index)
        ;
        $this->mappingBuilder->expects($this->any())
            ->method('buildIndexMapping')
            ->with($config)
            ->willReturn($mapping)
        ;

        return $index;
    }
}
