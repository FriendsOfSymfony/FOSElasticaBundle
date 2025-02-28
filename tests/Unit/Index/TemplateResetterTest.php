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

use Elastica\Client;
use Elastica\Request;
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
     * @var Client&MockObject
     */
    private $client;

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
        $this->client = $this->createMock(Client::class);
        $this->templateManager = $this->createMock(IndexTemplateManager::class);
        $this->resetter = new TemplateResetter(
            $this->configManager,
            $this->mappingBuilder,
            $this->client,
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
        $this->client
            ->expects($this->never())
            ->method('request')
            ->with($this->any(), Request::DELETE)
        ;

        // act
        $this->resetter->resetAllIndexes();
    }

    public function testResetAllIndexesAndDelete()
    {
        // assemble
        $names = ['first_template'];
        $mapping = ['properties' => []];
        $this->configManager
            ->method('getIndexNames')
            ->willReturn($names)
        ;
        $indexTemplateConfig = $this->createMock(IndexTemplateConfig::class);
        $this->configManager
            ->method('getIndexConfiguration')
            ->with('first_template')
            ->willReturn($indexTemplateConfig)
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
        $this->client
            ->expects($this->once())
            ->method('request')
            ->with('first_template/', Request::DELETE)
        ;
        $indexTemplateConfig
            ->expects($this->once())
            ->method('getIndexPatterns')
            ->willReturn(['first_template'])
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
        $this->client
            ->expects($this->never())
            ->method('request')
            ->with($this->any(), Request::DELETE)
        ;

        // act
        $this->resetter->resetIndex($name);
    }

    public function testResetIndexIndexeAndDelete()
    {
        // assemble
        $name = 'first_template';
        $mapping = ['properties' => []];
        $indexTemplateConfig = $this->createMock(IndexTemplateConfig::class);
        $this->configManager
            ->method('getIndexConfiguration')
            ->with('first_template')
            ->willReturn($indexTemplateConfig)
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
        $this->client
            ->expects($this->once())
            ->method('request')
            ->with('first_template/', Request::DELETE)
        ;
        $indexTemplateConfig
            ->expects($this->once())
            ->method('getIndexPatterns')
            ->willReturn(['first_template'])
        ;

        // act
        $this->resetter->resetIndex($name, true);
    }

    public function testDeleteTemplateIndexes()
    {
        // assemble
        $name = 'some_template';
        $template = $this->createMock(IndexTemplateConfig::class);

        // assert
        $template
            ->expects($this->once())
            ->method('getIndexPatterns')
            ->willReturn([$name])
        ;

        $this->client
            ->expects($this->once())
            ->method('request')
            ->with('some_template/', Request::DELETE)
        ;

        $this->resetter->deleteTemplateIndexes($template);
    }
}
