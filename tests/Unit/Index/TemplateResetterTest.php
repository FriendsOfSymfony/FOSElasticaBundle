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
use Elastica\Request;
use FOS\ElasticaBundle\Configuration\IndexTemplateConfig;
use FOS\ElasticaBundle\Configuration\ManagerInterface;
use FOS\ElasticaBundle\Elastica\IndexTemplate;
use FOS\ElasticaBundle\Index\IndexTemplateManager;
use FOS\ElasticaBundle\Index\MappingBuilder;
use FOS\ElasticaBundle\Index\ResetterInterface;
use FOS\ElasticaBundle\Index\TemplateResetter;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

/**
 * @author Dmitry Balabka <dmitry.balabka@intexsys.lv>
 */
class TemplateResetterTest extends TestCase
{
    /**
     * @var ManagerInterface
     */
    private $configManager;

    /**
     * @var MappingBuilder
     */
    private $mappingBuilder;

    /**
     * @var Client
     */
    private $client;

    /**
     * @var IndexTemplateManager
     */
    private $templateManager;

    /**
     * @var TemplateResetter
     */
    private $resetter;

    protected function setUp(): void
    {
        $this->configManager = $this->prophesize(ManagerInterface::class);
        $this->mappingBuilder = $this->prophesize(MappingBuilder::class);
        $this->client = $this->prophesize(Client::class);
        $this->templateManager = $this->prophesize(IndexTemplateManager::class);
        $this->resetter = new TemplateResetter(
            $this->configManager->reveal(),
            $this->mappingBuilder->reveal(),
            $this->client->reveal(),
            $this->templateManager->reveal()
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
        $this->configManager->getIndexNames()
            ->willReturn($names);
        $this->configManager->getIndexConfiguration('first_template')
            ->willReturn($indexTemplateConfig = $this->prophesize(IndexTemplateConfig::class)->reveal());
        $indexTemplate = $this->prophesize(IndexTemplate::class);
        $this->templateManager->getIndexTemplate('first_template')
            ->willReturn($indexTemplate->reveal());
        $this->mappingBuilder->buildIndexTemplateMapping($indexTemplateConfig)
            ->willReturn($mapping);

        // assert
        $indexTemplate->create($mapping)
            ->shouldBeCalled();
        $this->client->request(Argument::any(), Request::DELETE)
            ->shouldNotBeCalled();

        // act
        $this->resetter->resetAllIndexes();
    }

    public function testResetAllIndexesAndDelete()
    {
        // assemble
        $names = ['first_template'];
        $mapping = ['properties' => []];
        $this->configManager->getIndexNames()
            ->willReturn($names);
        $indexTemplateConfig = $this->prophesize(IndexTemplateConfig::class);
        $this->configManager->getIndexConfiguration('first_template')
            ->willReturn($indexTemplateConfig->reveal());
        $indexTemplate = $this->prophesize(IndexTemplate::class);
        $this->templateManager->getIndexTemplate('first_template')
            ->willReturn($indexTemplate->reveal());
        $this->mappingBuilder->buildIndexTemplateMapping($indexTemplateConfig)
            ->willReturn($mapping);

        // assert
        $indexTemplate->create($mapping)
            ->shouldBeCalled();
        $this->client->request('first_template/', Request::DELETE)
            ->shouldBeCalled();
        $indexTemplateConfig->getTemplate()
            ->shouldBeCalled()
            ->willReturn('first_template');

        // act
        $this->resetter->resetAllIndexes(true);
    }

    public function testResetIndex()
    {
        // assemble
        $name = 'first_template';
        $mapping = ['properties' => []];
        $this->configManager->getIndexConfiguration('first_template')
            ->willReturn($indexTemplateConfig = $this->prophesize(IndexTemplateConfig::class)->reveal());
        $indexTemplate = $this->prophesize(IndexTemplate::class);
        $this->templateManager->getIndexTemplate('first_template')
            ->willReturn($indexTemplate->reveal());
        $this->mappingBuilder->buildIndexTemplateMapping($indexTemplateConfig)
            ->willReturn($mapping);

        // assert
        $indexTemplate->create($mapping)
            ->shouldBeCalled();
        $this->client->request(Argument::any(), Request::DELETE)
            ->shouldNotBeCalled();

        // act
        $this->resetter->resetIndex($name);
    }

    public function testResetIndexIndexeAndDelete()
    {
        // assemble
        $name = 'first_template';
        $mapping = ['properties' => []];
        $indexTemplateConfig = $this->prophesize(IndexTemplateConfig::class);
        $this->configManager->getIndexConfiguration('first_template')
            ->willReturn($indexTemplateConfig->reveal());
        $indexTemplate = $this->prophesize(IndexTemplate::class);
        $this->templateManager->getIndexTemplate('first_template')
            ->willReturn($indexTemplate->reveal());
        $this->mappingBuilder->buildIndexTemplateMapping($indexTemplateConfig)
            ->willReturn($mapping);

        // assert
        $indexTemplate->create($mapping)
            ->shouldBeCalled();
        $this->client->request('first_template/', Request::DELETE)
            ->shouldBeCalled();
        $indexTemplateConfig->getTemplate()
            ->shouldBeCalled()
            ->willReturn('first_template');

        // act
        $this->resetter->resetIndex($name, true);
    }

    public function testDeleteTemplateIndexes()
    {
        // assemble
        $name = 'some_template';
        $template = $this->prophesize(IndexTemplateConfig::class);

        // assert
        $template->getTemplate()
            ->shouldBeCalled()
            ->willReturn($name);
        $this->client->request('some_template/', Request::DELETE)
            ->shouldBeCalled();

        $this->resetter->deleteTemplateIndexes($template->reveal());
    }
}
