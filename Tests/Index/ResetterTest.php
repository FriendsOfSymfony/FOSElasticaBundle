<?php

namespace FOS\ElasticaBundle\Tests\Index;

use Elastica\Type;
use FOS\ElasticaBundle\Configuration\ConfigManager;
use FOS\ElasticaBundle\Configuration\IndexConfig;
use FOS\ElasticaBundle\Configuration\IndexTemplateConfig;
use FOS\ElasticaBundle\Configuration\TypeConfig;
use FOS\ElasticaBundle\Elastica\Index;
use Elastica\IndexTemplate;
use FOS\ElasticaBundle\Event\IndexResetEvent;
use FOS\ElasticaBundle\Event\TypeResetEvent;
use FOS\ElasticaBundle\Index\AliasProcessor;
use FOS\ElasticaBundle\Index\IndexManager;
use FOS\ElasticaBundle\Index\MappingBuilder;
use FOS\ElasticaBundle\Index\Resetter;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ResetterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Resetter
     */
    private $resetter;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|AliasProcessor
     */
    private $aliasProcessor;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ConfigManager
     */
    private $configManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Elastica\Client
     */
    private $elasticaClient;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|IndexManager
     */
    private $indexManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|MappingBuilder
     */
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

    public function testResetAllIndexTemplates()
    {
        $indexTemplate = 'index_template1';

        $config = array(
            'template' => 't*'
        );
        $indexTemplateConfig = new IndexTemplateConfig($indexTemplate, array(), $config);
        $this->mockIndexTemplate($indexTemplate, $indexTemplateConfig);

        $this->configManager->expects($this->once())
            ->method('getIndexTemplatesNames')
            ->will($this->returnValue(array($indexTemplate)));

        $this->elasticaClient->expects($this->exactly(1))
            ->method('request')
            ->withConsecutive(
                array('/_template/index_template1', 'PUT', array(), array())
            );

        $this->resetter->resetAllTemplates();
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

    public function testResetType()
    {
        $typeConfig = new TypeConfig('type', array(), array());
        $this->mockType('type', 'index', $typeConfig);

        $this->dispatcherExpects(array(
            array(TypeResetEvent::PRE_TYPE_RESET, $this->isInstanceOf('FOS\\ElasticaBundle\\Event\\TypeResetEvent')),
            array(TypeResetEvent::POST_TYPE_RESET, $this->isInstanceOf('FOS\\ElasticaBundle\\Event\\TypeResetEvent'))
        ));

        $this->elasticaClient->expects($this->exactly(2))
            ->method('request')
            ->withConsecutive(
                array('index/type/', 'DELETE'),
                array('index/type/_mapping', 'PUT', array('type' => array()), array())
            );

        $this->resetter->resetIndexType('index', 'type');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testNonExistantResetType()
    {
        $this->configManager->expects($this->once())
            ->method('getTypeConfiguration')
            ->with('index', 'nonExistant')
            ->will($this->throwException(new \InvalidArgumentException));

        $this->indexManager->expects($this->never())
            ->method('getIndex');

        $this->resetter->resetIndexType('index', 'nonExistant');
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

    private function mockIndexTemplate($indexTemplateName, IndexTemplateConfig $config, $mapping = array())
    {
        $this->configManager->expects($this->atLeast(1))
            ->method('getIndexTemplateConfiguration')
            ->with($indexTemplateName)
            ->will($this->returnValue($config));
        $index = new IndexTemplate($this->elasticaClient, $indexTemplateName);
        $this->indexManager->expects($this->any())
            ->method('getIndexTemplate')
            ->with($indexTemplateName)
            ->willReturn($index);
        $this->mappingBuilder->expects($this->any())
            ->method('buildIndexTemplateMapping')
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
