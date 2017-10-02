<?php
/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Tests\Command;

use FOS\ElasticaBundle\Command\CreateCommand;
use FOS\ElasticaBundle\Configuration\ConfigManager;
use FOS\ElasticaBundle\Configuration\IndexConfig;
use FOS\ElasticaBundle\Elastica\Index;
use FOS\ElasticaBundle\Index\AliasProcessor;
use FOS\ElasticaBundle\Index\IndexManager;
use FOS\ElasticaBundle\Index\MappingBuilder;
use Symfony\Component\DependencyInjection\Container;

/**
 * Create command test
 *
 * @author Oleg Andreyev <oleg.andreyev@intexsys.lv>
 */
class CreateCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var IndexManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $indexManager;

    /**
     * @var MappingBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mappingBuilder;

    /**
     * @var ConfigManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configManager;

    /**
     * @var AliasProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $aliasProcessor;

    /**
     * @var CreateCommand
     */
    private $command;

    /**
     * @var IndexConfig
     */
    private $indexConfig;

    /**
     * @var Index
     */
    private $index;

    /**
     * {@inheritdoc}
     */
    public function setup()
    {
        $container = new Container();
        $this->indexManager = $this->getMockBuilder('\FOS\ElasticaBundle\Index\IndexManager')
            ->disableOriginalConstructor()
            ->getMock();
        $this->mappingBuilder = $this->getMockBuilder('FOS\ElasticaBundle\Index\MappingBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $this->configManager = $this->getMockBuilder('FOS\ElasticaBundle\Configuration\ConfigManager')
            ->disableOriginalConstructor()
            ->getMock();
        $this->aliasProcessor = $this->getMockBuilder('FOS\ElasticaBundle\Index\AliasProcessor')
            ->disableOriginalConstructor()
            ->getMock();
        $this->indexConfig = $this->getMockBuilder('\FOS\ElasticaBundle\Configuration\IndexConfig')
            ->disableOriginalConstructor()
            ->getMock();
        $this->index = $this->getMockBuilder('\FOS\ElasticaBundle\Elastica\Index')
            ->disableOriginalConstructor()
            ->getMock();

        $container->set('fos_elastica.index_manager', $this->indexManager);
        $container->set('fos_elastica.mapping_builder', $this->mappingBuilder);
        $container->set('fos_elastica.config_manager', $this->configManager);
        $container->set('fos_elastica.alias_processor', $this->aliasProcessor);

        $this->command = new CreateCommand();
        $this->command->setContainer($container);
    }

    /**
     * Test execute with index provided and with alias
     *
     * @return void
     */
    public function testExecuteWithIndexProvidedAndWithAlias()
    {
        $input = $this->getMockForAbstractClass('\Symfony\Component\Console\Input\InputInterface');
        $output = $this->getMockForAbstractClass('\Symfony\Component\Console\Output\OutputInterface');

        $indexName = 'foo';
        $mapping = ['mapping'];

        $input->expects($this->once())->method('getOption')->with('index')->willReturn($indexName);
        $output->expects($this->once())->method('writeln');
        $this->configManager->expects($this->once())->method('getIndexConfiguration')->with($indexName)->willReturn($this->indexConfig);
        $this->indexManager->expects($this->once())->method('getIndex')->with($indexName)->willReturn($this->index);
        $this->indexConfig->expects($this->once())->method('isUseAlias')->willReturn(true);
        $this->aliasProcessor->expects($this->once())->method('setRootName')->with($this->indexConfig, $this->index);
        $this->mappingBuilder->expects($this->once())->method('buildIndexMapping')->with($this->indexConfig)->willReturn($mapping);
        $this->index->expects($this->once())->method('create')->with(['mapping'], false);

        $this->command->run($input, $output);
    }

    /**
     * Test execute with index provided and without alias
     *
     * @return void
     */
    public function testExecuteWithIndexProvidedAndWithoutAlias()
    {
        $input = $this->getMockForAbstractClass('\Symfony\Component\Console\Input\InputInterface');
        $output = $this->getMockForAbstractClass('\Symfony\Component\Console\Output\OutputInterface');

        $indexName = 'foo';
        $mapping = ['mapping'];

        $input->expects($this->once())->method('getOption')->with('index')->willReturn($indexName);
        $output->expects($this->once())->method('writeln');
        $this->configManager->expects($this->once())->method('getIndexConfiguration')->with($indexName)->willReturn($this->indexConfig);
        $this->indexManager->expects($this->once())->method('getIndex')->with($indexName)->willReturn($this->index);
        $this->indexConfig->expects($this->once())->method('isUseAlias')->willReturn(false);
        $this->aliasProcessor->expects($this->never())->method('setRootName');
        $this->mappingBuilder->expects($this->once())->method('buildIndexMapping')->with($this->indexConfig)->willReturn($mapping);
        $this->index->expects($this->once())->method('create')->with(['mapping'], false);

        $this->command->run($input, $output);
    }

    public function testExecuteAllIndices()
    {
        $input = $this->getMockForAbstractClass('\Symfony\Component\Console\Input\InputInterface');
        $output = $this->getMockForAbstractClass('\Symfony\Component\Console\Output\OutputInterface');
        $indexConfig1 = clone $this->indexConfig;
        $indexConfig2 = clone $this->indexConfig;
        $index1 = clone $this->index;
        $index2 = clone $this->index;

        $indexName = null;
        $indices = ['foo', 'bar'];
        $mapping = ['mapping'];

        $input->expects($this->once())->method('getOption')->with('index')->willReturn($indexName);
        $this->indexManager->expects($this->once())->method('getAllIndexes')->willReturn(array_flip($indices));
        $output->expects($this->exactly(2))->method('writeln');

        $this->configManager->expects($this->exactly(2))->method('getIndexConfiguration')
            ->withConsecutive(['foo'], ['bar'])
            ->willReturnOnConsecutiveCalls($indexConfig1, $indexConfig2);

        $this->indexManager->expects($this->exactly(2))->method('getIndex')
            ->withConsecutive(['foo'], ['bar'])
            ->willReturnOnConsecutiveCalls($index1, $index2);

        $indexConfig1->expects($this->once())->method('isUseAlias')->willReturn(false);
        $indexConfig2->expects($this->once())->method('isUseAlias')->willReturn(false);

        $this->aliasProcessor->expects($this->never())->method('setRootName');

        $this->mappingBuilder->expects($this->exactly(2))->method('buildIndexMapping')
            ->withConsecutive([$indexConfig1], [$indexConfig2])
            ->willReturn($mapping);

        $index1->expects($this->once())->method('create')->with(['mapping'], false);
        $index2->expects($this->once())->method('create')->with(['mapping'], false);

        $this->command->run($input, $output);
    }
}
