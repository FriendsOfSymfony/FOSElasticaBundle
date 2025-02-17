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

use Elastic\Elasticsearch\Endpoints\Indices;
use Elastica\Client;
use FOS\ElasticaBundle\Configuration\IndexTemplateConfig;
use FOS\ElasticaBundle\Configuration\ManagerInterface;
use FOS\ElasticaBundle\Elastica\IndexTemplate;
use FOS\ElasticaBundle\Index\IndexTemplateManager;
use FOS\ElasticaBundle\Index\MappingBuilder;
use FOS\ElasticaBundle\Index\ResetterInterface;
use FOS\ElasticaBundle\Index\TemplateResetter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @author Dmitry Balabka <dmitry.balabka@intexsys.lv>
 *
 * @internal
 */
class TemplateResetterTest extends TestCase
{
    /**
     * @var ManagerInterface&MockObject
     */
    private $configManager;

    /**
     * @var MappingBuilder&MockObject
     */
    private $mappingBuilder;

    /**
     * @var IndexTemplateManager&MockObject
     */
    private $templateManager;

    /**
     * @var TemplateResetter
     */
    private $resetter;

    protected function setUp(): void
    {
        $this->configManager = $this->createMock(ManagerInterface::class);
        $this->mappingBuilder = $this->createMock(MappingBuilder::class);
        $this->templateManager = $this->createMock(IndexTemplateManager::class);
        $this->resetter = new TemplateResetter(
            $this->configManager,
            $this->mappingBuilder,
            $this->templateManager
        );
    }

    public function testResetterImplementsResetterInterface()
    {
        $this->assertInstanceOf(ResetterInterface::class, $this->resetter);
    }

    public function testResetAllIndexes()
    {
        // assemble
        $names = ['first_template'];
        $mapping = ['properties' => []];
        $this->configManager
            ->method('getIndexNames')
            ->willReturn($names)
        ;
        $this->configManager
            ->method('getIndexConfiguration')
            ->with('first_template')
            ->willReturn($indexTemplateConfig = $this->createStub(IndexTemplateConfig::class))
        ;
        $indexTemplate = $this->createMock(IndexTemplate::class);
        $this->templateManager
            ->method('getIndexTemplate')
            ->with('first_template')
            ->willReturn($indexTemplate)
        ;
        $this->mappingBuilder
            ->method('buildIndexTemplateMapping')
            ->with($indexTemplateConfig)
            ->willReturn($mapping)
        ;

        // assert
        $indexTemplate
            ->expects($this->once())
            ->method('create')
            ->with($mapping)
        ;
        $indexTemplate
            ->expects($this->never())
            ->method('delete')
        ;

        // act
        $this->resetter->resetAllIndexes();
    }

    public function testResetAllIndexesAndDelete()
    {
        // assemble
        $indexName = 'templated_index';
        $templateNames = ['first_template'];
        $names = [$indexName];
        $mapping = ['properties' => []];
        $this->configManager
            ->method('getIndexNames')
            ->willReturn($names)
        ;
        $indexTemplateConfig = $this->createMock(IndexTemplateConfig::class);
        $this->configManager
            ->method('getIndexConfiguration')
            ->with($indexName)
            ->willReturn($indexTemplateConfig)
        ;
        $indexTemplate = $this->createMock(IndexTemplate::class);
        $this->templateManager
            ->method('getIndexTemplate')
            ->with($indexName)
            ->willReturn($indexTemplate)
        ;
        $this->mappingBuilder
            ->method('buildIndexTemplateMapping')
            ->with($indexTemplateConfig)
            ->willReturn($mapping)
        ;

        // assert
        $indexTemplate
            ->expects($this->once())
            ->method('create')
            ->with($mapping)
        ;
        $indexTemplate
            ->expects($this->once())
            ->method('delete')
            ->with()
        ;
        $indices = $this->createMock(Indices::class);
        $indices->expects($this->once())
            ->method('delete')
            ->with(['index' => $templateNames[0].'/']);
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('indices')
            ->willReturn($indices);
        $indexTemplate->expects($this->once())
            ->method('getClient')
            ->willReturn($client);
        $indexTemplateConfig
            ->expects($this->once())
            ->method('getIndexPatterns')
            ->willReturn($templateNames)
        ;

        // act
        $this->resetter->resetAllIndexes(true);
    }

    public function testResetIndex()
    {
        // assemble
        $name = 'first_template';
        $mapping = ['properties' => []];
        $this->configManager
            ->method('getIndexConfiguration')
            ->with('first_template')
            ->willReturn($indexTemplateConfig = $this->createStub(IndexTemplateConfig::class))
        ;
        $indexTemplate = $this->createMock(IndexTemplate::class);
        $this->templateManager
            ->method('getIndexTemplate')
            ->with('first_template')
            ->willReturn($indexTemplate)
        ;
        $this->mappingBuilder
            ->method('buildIndexTemplateMapping')
            ->with($indexTemplateConfig)
            ->willReturn($mapping)
        ;

        // assert
        $indexTemplate
            ->expects($this->once())
            ->method('create')
            ->with($mapping)
        ;
        $indexTemplate
            ->expects($this->never())
            ->method('delete')
        ;

        // act
        $this->resetter->resetIndex($name);
    }

    public function testResetIndexIndexeAndDelete()
    {
        // assemble
        $indexName = 'templated_index';
        $templateNames = ['first_template'];
        $mapping = ['properties' => []];
        $indexTemplateConfig = $this->createMock(IndexTemplateConfig::class);
        $this->configManager
            ->method('getIndexConfiguration')
            ->with($indexName)
            ->willReturn($indexTemplateConfig)
        ;
        $indexTemplate = $this->createMock(IndexTemplate::class);
        $this->templateManager
            ->method('getIndexTemplate')
            ->with($indexName)
            ->willReturn($indexTemplate)
        ;
        $this->mappingBuilder
            ->method('buildIndexTemplateMapping')
            ->with($indexTemplateConfig)
            ->willReturn($mapping)
        ;

        // assert
        $indexTemplate
            ->expects($this->once())
            ->method('create')
            ->with($mapping)
        ;
        $indexTemplate
            ->expects($this->once())
            ->method('delete')
            ->with()
        ;
        $indexTemplateConfig
            ->expects($this->once())
            ->method('getIndexPatterns')
            ->willReturn($templateNames)
        ;
        $indices = $this->createMock(Indices::class);
        $indices->expects($this->once())
            ->method('delete')
            ->with(['index' => $templateNames[0].'/'])
        ;
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('indices')
            ->willReturn($indices)
        ;
        $indexTemplate->expects($this->once())
            ->method('getClient')
            ->willReturn($client)
        ;

        // act
        $this->resetter->resetIndex($indexName, true);
    }

    public function testDeleteTemplateIndexes()
    {
        // assemble
        $templateNames = ['some_template'];
        $template = $this->createMock(IndexTemplateConfig::class);

        // assert
        $template
            ->expects($this->once())
            ->method('getIndexPatterns')
            ->willReturn($templateNames)
        ;

        $indices = $this->createMock(Indices::class);
        $indices->expects($this->once())
            ->method('delete')
            ->with(['index' => $templateNames[0].'/'])
        ;
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('indices')
            ->willReturn($indices)
        ;

        $this->resetter->deleteTemplateIndexes($template, $client);
    }
}
