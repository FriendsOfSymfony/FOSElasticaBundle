<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <https://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Tests\Unit\Command;

use FOS\ElasticaBundle\Command\CreateCommand;
use FOS\ElasticaBundle\Configuration\ConfigManager;
use FOS\ElasticaBundle\Configuration\IndexConfig;
use FOS\ElasticaBundle\Elastica\Index;
use FOS\ElasticaBundle\Index\AliasProcessor;
use FOS\ElasticaBundle\Index\IndexManager;
use FOS\ElasticaBundle\Index\MappingBuilder;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Oleg Andreyev <oleg.andreyev@intexsys.lv>
 *
 * @internal
 */
class CreateCommandTest extends TestCase
{
    /**
     * @var IndexManager|\PHPUnit\Framework\MockObject\MockObject
     */
    private $indexManager;

    /**
     * @var MappingBuilder|\PHPUnit\Framework\MockObject\MockObject
     */
    private $mappingBuilder;

    /**
     * @var ConfigManager|\PHPUnit\Framework\MockObject\MockObject
     */
    private $configManager;

    /**
     * @var AliasProcessor|\PHPUnit\Framework\MockObject\MockObject
     */
    private $aliasProcessor;

    /**
     * @var CreateCommand
     */
    private $command;

    /**
     * @var IndexConfig|\PHPUnit\Framework\MockObject\MockObject
     */
    private $indexConfig;

    /**
     * @var Index|\PHPUnit\Framework\MockObject\MockObject
     */
    private $index;

    protected function setUp(): void
    {
        $this->indexManager = $this->createMock(IndexManager::class);
        $this->mappingBuilder = $this->createMock(MappingBuilder::class);
        $this->configManager = $this->createMock(ConfigManager::class);
        $this->aliasProcessor = $this->createMock(AliasProcessor::class);
        $this->indexConfig = $this->createMock(IndexConfig::class);
        $this->index = $this->createMock(Index::class);

        $this->command = new CreateCommand(
            $this->indexManager,
            $this->mappingBuilder,
            $this->configManager,
            $this->aliasProcessor
        );
    }

    public function testExecuteWithIndexProvidedAndWithAlias()
    {
        $input = $this->createMock(InputInterface::class);
        $output = $this->createMock(OutputInterface::class);

        $indexName = 'foo';
        $mapping = ['mapping'];

        $input
            ->expects($this->exactly(2))
            ->method('getOption')
            ->withConsecutive(['index'], ['no-alias'])
            ->willReturnOnConsecutiveCalls($indexName, false)
        ;
        $output->expects($this->once())->method('writeln');
        $this->configManager->expects($this->once())->method('getIndexConfiguration')->with($indexName)->willReturn($this->indexConfig);
        $this->indexManager->expects($this->once())->method('getIndex')->with($indexName)->willReturn($this->index);
        $this->indexConfig->expects($this->exactly(2))->method('isUseAlias')->willReturn(true);
        $this->indexConfig->expects($this->once())->method('getElasticSearchName')->willReturn($indexName);
        $this->aliasProcessor->expects($this->once())->method('setRootName')->with($this->indexConfig, $this->index);
        $this->mappingBuilder->expects($this->once())->method('buildIndexMapping')->with($this->indexConfig)->willReturn($mapping);
        $this->index->expects($this->once())->method('create')->with(['mapping'], []);
        $this->index->expects($this->once())->method('addAlias')->with($indexName);

        $this->command->run($input, $output);
    }

    public function testExecuteWithIndexProvidedAndWithAliasButDisabled()
    {
        $input = $this->createMock(InputInterface::class);
        $output = $this->createMock(OutputInterface::class);

        $indexName = 'foo';
        $mapping = ['mapping'];

        $input
            ->expects($this->exactly(2))
            ->method('getOption')
            ->withConsecutive(['index'], ['no-alias'])
            ->willReturnOnConsecutiveCalls($indexName, true)
        ;
        $output->expects($this->once())->method('writeln');
        $this->configManager->expects($this->once())->method('getIndexConfiguration')->with($indexName)->willReturn($this->indexConfig);
        $this->indexManager->expects($this->once())->method('getIndex')->with($indexName)->willReturn($this->index);
        $this->indexConfig->expects($this->exactly(2))->method('isUseAlias')->willReturn(true);
        $this->aliasProcessor->expects($this->once())->method('setRootName')->with($this->indexConfig, $this->index);
        $this->mappingBuilder->expects($this->once())->method('buildIndexMapping')->with($this->indexConfig)->willReturn($mapping);
        $this->index->expects($this->once())->method('create')->with(['mapping'], []);
        $this->index->expects($this->never())->method('addAlias')->with($indexName);

        $this->command->run($input, $output);
    }

    public function testExecuteWithIndexProvidedAndWithoutAlias()
    {
        $input = $this->createMock(InputInterface::class);
        $output = $this->createMock(OutputInterface::class);

        $indexName = 'foo';
        $mapping = ['mapping'];

        $input->expects($this->once())->method('getOption')->with('index')->willReturn($indexName);
        $output->expects($this->once())->method('writeln');
        $this->configManager->expects($this->once())->method('getIndexConfiguration')->with($indexName)->willReturn($this->indexConfig);
        $this->indexManager->expects($this->once())->method('getIndex')->with($indexName)->willReturn($this->index);
        $this->indexConfig->expects($this->exactly(2))->method('isUseAlias')->willReturn(false);
        $this->aliasProcessor->expects($this->never())->method('setRootName');
        $this->mappingBuilder->expects($this->once())->method('buildIndexMapping')->with($this->indexConfig)->willReturn($mapping);
        $this->index->expects($this->once())->method('create')->with(['mapping'], []);
        $this->index->expects($this->never())->method('addAlias');

        $this->command->run($input, $output);
    }

    public function testExecuteAllIndices()
    {
        $input = $this->createMock(InputInterface::class);
        $output = $this->createMock(OutputInterface::class);
        $indexConfig1 = clone $this->indexConfig;
        $indexConfig2 = clone $this->indexConfig;
        $index1 = clone $this->index;
        $index2 = clone $this->index;

        $indexName = null;
        $indices = ['foo', 'bar'];
        $mapping = ['mapping'];

        $input->expects($this->once())->method('getOption')->with('index')->willReturn($indexName);
        $this->indexManager->expects($this->once())->method('getAllIndexes')->willReturn(\array_flip($indices));
        $output->expects($this->exactly(2))->method('writeln');

        $this->configManager->expects($this->exactly(2))->method('getIndexConfiguration')
            ->withConsecutive(['foo'], ['bar'])
            ->willReturnOnConsecutiveCalls($indexConfig1, $indexConfig2)
        ;

        $this->indexManager->expects($this->exactly(2))->method('getIndex')
            ->withConsecutive(['foo'], ['bar'])
            ->willReturnOnConsecutiveCalls($index1, $index2)
        ;

        $indexConfig1->expects($this->exactly(2))->method('isUseAlias')->willReturn(false);
        $indexConfig2->expects($this->exactly(2))->method('isUseAlias')->willReturn(false);

        $this->aliasProcessor->expects($this->never())->method('setRootName');

        $this->mappingBuilder->expects($this->exactly(2))->method('buildIndexMapping')
            ->withConsecutive([$indexConfig1], [$indexConfig2])
            ->willReturn($mapping)
        ;

        $index1->expects($this->once())->method('create')->with(['mapping'], []);
        $index1->expects($this->never())->method('addAlias');
        $index2->expects($this->once())->method('create')->with(['mapping'], []);
        $index2->expects($this->never())->method('addAlias');

        $this->command->run($input, $output);
    }
}
