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
        $templateName = 'index_template';
        $indexPatterns = ['first_template_index'];
        $names = [$templateName];
        $mapping = ['properties' => []];
        $this->configManager
            ->method('getIndexNames')
            ->willReturn($names)
        ;
        $indexTemplateConfig = $this->createMock(IndexTemplateConfig::class);
        $indexTemplateConfig
            ->method('getName')
            ->willReturn($templateName)
        ;
        $this->configManager
            ->method('getIndexConfiguration')
            ->with($templateName)
            ->willReturn($indexTemplateConfig)
        ;
        $indexTemplate = $this->createMock(IndexTemplate::class);
        $this->templateManager
            ->method('getIndexTemplate')
            ->with($templateName)
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
            ->with(['index' => $indexPatterns[0].'/'])
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
        $indexTemplateConfig
            ->expects($this->once())
            ->method('getIndexPatterns')
            ->willReturn($indexPatterns)
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
        $templateName = 'index_template';
        $indexPatterns = ['first_template_index'];
        $mapping = ['properties' => []];
        $indexTemplateConfig = $this->createMock(IndexTemplateConfig::class);
        $this->configManager
            ->method('getIndexConfiguration')
            ->with($templateName)
            ->willReturn($indexTemplateConfig)
        ;
        $indexTemplate = $this->createMock(IndexTemplate::class);
        $this->templateManager
            ->method('getIndexTemplate')
            ->with($templateName)
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
            ->willReturn($indexPatterns)
        ;
        $indexTemplateConfig
            ->method('getName')
            ->willReturn($templateName)
        ;
        $indices = $this->createMock(Indices::class);
        $indices->expects($this->once())
            ->method('delete')
            ->with(['index' => $indexPatterns[0].'/'])
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
        $this->resetter->resetIndex($templateName, true);
    }

    public function testDeleteTemplateIndexes()
    {
        // assemble
        $templateName = 'some_template';
        $indexPatterns = ['some_template_index'];
        $template = $this->createMock(IndexTemplateConfig::class);

        // assert
        $template
            ->expects($this->once())
            ->method('getIndexPatterns')
            ->willReturn($indexPatterns)
        ;
        $template
            ->expects($this->once())
            ->method('getName')
            ->willReturn($templateName)
        ;

        $indices = $this->createMock(Indices::class);
        $indices->expects($this->once())
            ->method('delete')
            ->with(['index' => $indexPatterns[0].'/'])
        ;
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('indices')
            ->willReturn($indices)
        ;

        $indexTemplate = $this->createMock(IndexTemplate::class);
        $indexTemplate->expects($this->once())
            ->method('getClient')
            ->willReturn($client)
        ;
        $this->templateManager
            ->method('getIndexTemplate')
            ->with($templateName)
            ->willReturn($indexTemplate)
        ;

        $this->resetter->deleteTemplateIndexes($template);
    }
}
