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

use Elastica\Client;
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
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ResetterTest extends TestCase
{
    /**
     * @var Resetter
     */
    private $resetter;

    private $aliasProcessor;
    private $configManager;
    private $dispatcher;
    private $elasticaClient;
    private $indexManager;
    private $mappingBuilder;

    protected function setUp()
    {
        $this->aliasProcessor = $this->createMock(AliasProcessor::class);
        $this->configManager = $this->createMock(ConfigManager::class);
        $this->dispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->elasticaClient = $this->createMock(Client::class);
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
        $indexConfig = new IndexConfig($indexName, [], []);
        $this->mockIndex($indexName, $indexConfig);

        $this->configManager->expects($this->once())
            ->method('getIndexNames')
            ->will($this->returnValue([$indexName]));

        $this->dispatcherExpects([
            [$this->isInstanceOf(PreIndexResetEvent::class)],
            [$this->isInstanceOf(PostIndexResetEvent::class)],
        ]);

        $this->elasticaClient->expects($this->exactly(2))
            ->method('requestEndpoint');

        $this->resetter->resetAllIndexes();
    }

    public function testResetIndex()
    {
        $indexConfig = new IndexConfig('index1', [], []);
        $this->mockIndex('index1', $indexConfig);

        $this->dispatcherExpects([
            [$this->isInstanceOf(PreIndexResetEvent::class)],
            [$this->isInstanceOf(PostIndexResetEvent::class)],
        ]);

        $this->elasticaClient->expects($this->exactly(2))
            ->method('requestEndpoint');

        $this->resetter->resetIndex('index1');
    }

    public function testResetIndexWithDifferentName()
    {
        $indexConfig = new IndexConfig('index1', [], [
            'elasticSearchName' => 'notIndex1',
        ]);
        $this->mockIndex('index1', $indexConfig);
        $this->dispatcherExpects([
            [$this->isInstanceOf(PreIndexResetEvent::class)],
            [$this->isInstanceOf(PostIndexResetEvent::class)],
        ]);

        $this->elasticaClient->expects($this->exactly(2))
            ->method('requestEndpoint');

        $this->resetter->resetIndex('index1');
    }

    public function testResetIndexWithDifferentNameAndAlias()
    {
        $indexConfig = new IndexConfig('index1', [], [
            'elasticSearchName' => 'notIndex1',
            'useAlias' => true,
        ]);
        $index = $this->mockIndex('index1', $indexConfig);
        $this->dispatcherExpects([
            [$this->isInstanceOf(PreIndexResetEvent::class)],
            [$this->isInstanceOf(PostIndexResetEvent::class)],
        ]);

        $this->aliasProcessor->expects($this->once())
            ->method('switchIndexAlias')
            ->with($indexConfig, $index, false);

        $this->elasticaClient->expects($this->exactly(2))
            ->method('requestEndpoint');

        $this->resetter->resetIndex('index1');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testFailureWhenMissingIndexDoesntDispatch()
    {
        $this->configManager->expects($this->once())
            ->method('getIndexConfiguration')
            ->with('nonExistant')
            ->will($this->throwException(new \InvalidArgumentException()));

        $this->indexManager->expects($this->never())
            ->method('getIndex');

        $this->resetter->resetIndex('nonExistant');
    }

    public function testPostPopulateWithoutAlias()
    {
        $this->mockIndex('index', new IndexConfig('index', [], []));

        $this->indexManager->expects($this->never())
            ->method('getIndex');
        $this->aliasProcessor->expects($this->never())
            ->method('switchIndexAlias');

        $this->resetter->switchIndexAlias('index');
    }

    public function testPostPopulate()
    {
        $indexConfig = new IndexConfig('index', [], ['useAlias' => true]);
        $index = $this->mockIndex('index', $indexConfig);

        $this->aliasProcessor->expects($this->once())
            ->method('switchIndexAlias')
            ->with($indexConfig, $index);

        $this->resetter->switchIndexAlias('index');
    }

    private function dispatcherExpects(array $events)
    {
        $expectation = $this->dispatcher->expects($this->exactly(count($events)))
            ->method('dispatch');

        call_user_func_array([$expectation, 'withConsecutive'], $events);
    }

    private function mockIndex($indexName, IndexConfig $config, $mapping = [])
    {
        $this->configManager->expects($this->atLeast(1))
            ->method('getIndexConfiguration')
            ->with($indexName)
            ->will($this->returnValue($config));
        $index = new Index($this->elasticaClient, $indexName);
        $this->indexManager->expects($this->any())
            ->method('getIndex')
            ->with($indexName)
            ->willReturn($index);
        $this->mappingBuilder->expects($this->any())
            ->method('buildIndexMapping')
            ->with($config)
            ->willReturn($mapping);

        return $index;
    }

    public function testResetterImplementsResetterInterface()
    {
        $this->assertInstanceOf(ResetterInterface::class, $this->resetter);
    }
}
