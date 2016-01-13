<?php

namespace FOS\ElasticaBundle\Tests\Index;

use Elastica\Exception\ResponseException;
use Elastica\Request;
use Elastica\Response;
use Elastica\Type;
use Elastica\Type\Mapping;
use FOS\ElasticaBundle\Configuration\IndexConfig;
use FOS\ElasticaBundle\Configuration\TypeConfig;
use FOS\ElasticaBundle\Elastica\Index;
use FOS\ElasticaBundle\Event\IndexResetEvent;
use FOS\ElasticaBundle\Event\TypeResetEvent;
use FOS\ElasticaBundle\Index\AliasProcessor;
use FOS\ElasticaBundle\Index\Resetter;

class ResetterTest extends \PHPUnit_Framework_TestCase
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

    public function testResetAllIndexes()
    {
        $indexName = 'index1';
        $indexConfig = new IndexConfig($indexName, array(), array());
        $this->mockIndex($indexName, $indexConfig);

        $this->configManager->expects($this->once())
            ->method('getIndexNames')
            ->will($this->returnValue(array($indexName)));

        $this->dispatcherExpects(array(
            array(IndexResetEvent::PRE_INDEX_RESET, $this->isInstanceOf('FOS\\ElasticaBundle\\Event\\IndexResetEvent')),
            array(IndexResetEvent::POST_INDEX_RESET, $this->isInstanceOf('FOS\\ElasticaBundle\\Event\\IndexResetEvent'))
        ));

        $this->elasticaClient->expects($this->exactly(2))
            ->method('request')
            ->withConsecutive(
                array('index1/', 'DELETE'),
                array('index1/', 'PUT', array(), array())
            );

        $this->resetter->resetAllIndexes();
    }

    public function testResetIndex()
    {
        $indexConfig = new IndexConfig('index1', array(), array());
        $this->mockIndex('index1', $indexConfig);

        $this->dispatcherExpects(array(
            array(IndexResetEvent::PRE_INDEX_RESET, $this->isInstanceOf('FOS\\ElasticaBundle\\Event\\IndexResetEvent')),
            array(IndexResetEvent::POST_INDEX_RESET, $this->isInstanceOf('FOS\\ElasticaBundle\\Event\\IndexResetEvent'))
        ));

        $this->elasticaClient->expects($this->exactly(2))
            ->method('request')
            ->withConsecutive(
                array('index1/', 'DELETE'),
                array('index1/', 'PUT', array(), array())
            );

        $this->resetter->resetIndex('index1');
    }

    public function testResetIndexWithDifferentName()
    {
        $indexConfig = new IndexConfig('index1', array(), array(
            'elasticSearchName' => 'notIndex1'
        ));
        $this->mockIndex('index1', $indexConfig);
        $this->dispatcherExpects(array(
            array(IndexResetEvent::PRE_INDEX_RESET, $this->isInstanceOf('FOS\\ElasticaBundle\\Event\\IndexResetEvent')),
            array(IndexResetEvent::POST_INDEX_RESET, $this->isInstanceOf('FOS\\ElasticaBundle\\Event\\IndexResetEvent'))
        ));

        $this->elasticaClient->expects($this->exactly(2))
            ->method('request')
            ->withConsecutive(
                array('index1/', 'DELETE'),
                array('index1/', 'PUT', array(), array())
            );

        $this->resetter->resetIndex('index1');
    }

    public function testResetIndexWithDifferentNameAndAlias()
    {
        $indexConfig = new IndexConfig('index1', array(), array(
            'elasticSearchName' => 'notIndex1',
            'useAlias' => true
        ));
        $index = $this->mockIndex('index1', $indexConfig);
        $this->dispatcherExpects(array(
            array(IndexResetEvent::PRE_INDEX_RESET, $this->isInstanceOf('FOS\\ElasticaBundle\\Event\\IndexResetEvent')),
            array(IndexResetEvent::POST_INDEX_RESET, $this->isInstanceOf('FOS\\ElasticaBundle\\Event\\IndexResetEvent'))
        ));

        $this->aliasProcessor->expects($this->once())
            ->method('switchIndexAlias')
            ->with($indexConfig, $index, false);

        $this->elasticaClient->expects($this->exactly(2))
            ->method('request')
            ->withConsecutive(
                array('index1/', 'DELETE'),
                array('index1/', 'PUT', array(), array())
            );

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
            ->will($this->throwException(new \InvalidArgumentException));

        $this->indexManager->expects($this->never())
            ->method('getIndex');

        $this->resetter->resetIndex('nonExistant');
    }

    public function testPostPopulateWithoutAlias()
    {
        $this->mockIndex('index', new IndexConfig('index', array(), array()));

        $this->indexManager->expects($this->never())
            ->method('getIndex');
        $this->aliasProcessor->expects($this->never())
            ->method('switchIndexAlias');

        $this->resetter->postPopulate('index');
    }

    public function testPostPopulate()
    {
        $indexConfig = new IndexConfig('index', array(), array( 'useAlias' => true));
        $index = $this->mockIndex('index', $indexConfig);

        $this->aliasProcessor->expects($this->once())
            ->method('switchIndexAlias')
            ->with($indexConfig, $index);

        $this->resetter->postPopulate('index');
    }

    private function dispatcherExpects(array $events)
    {
        $expectation = $this->dispatcher->expects($this->exactly(count($events)))
            ->method('dispatch');

        call_user_func_array(array($expectation, 'withConsecutive'), $events);
    }

    private function mockIndex($indexName, IndexConfig $config, $mapping = array())
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

    private function mockType($typeName, $indexName, TypeConfig $config, $mapping = array())
    {
        $this->configManager->expects($this->atLeast(1))
            ->method('getTypeConfiguration')
            ->with($indexName, $typeName)
            ->will($this->returnValue($config));
        $index = new Index($this->elasticaClient, $indexName);
        $this->indexManager->expects($this->once())
            ->method('getIndex')
            ->with($indexName)
            ->willReturn($index);
        $this->mappingBuilder->expects($this->once())
            ->method('buildTypeMapping')
            ->with($config)
            ->willReturn($mapping);

        return $index;
    }

    protected function setUp()
    {
        $this->aliasProcessor = $this->getMockBuilder('FOS\\ElasticaBundle\\Index\\AliasProcessor')
            ->disableOriginalConstructor()
            ->getMock();
        $this->configManager = $this->getMockBuilder('FOS\\ElasticaBundle\\Configuration\\ConfigManager')
            ->disableOriginalConstructor()
            ->getMock();
        $this->dispatcher = $this->getMockBuilder('Symfony\\Component\\EventDispatcher\\EventDispatcherInterface')
            ->getMock();
        $this->elasticaClient = $this->getMockBuilder('Elastica\\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $this->indexManager = $this->getMockBuilder('FOS\\ElasticaBundle\\Index\\IndexManager')
            ->disableOriginalConstructor()
            ->getMock();
        $this->mappingBuilder = $this->getMockBuilder('FOS\\ElasticaBundle\\Index\\MappingBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $this->resetter = new Resetter(
            $this->configManager,
            $this->indexManager,
            $this->aliasProcessor,
            $this->mappingBuilder,
            $this->dispatcher
        );
    }
}
