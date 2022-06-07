<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <https://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Command;

use FOS\ElasticaBundle\Configuration\ConfigManager;
use FOS\ElasticaBundle\Index\AliasProcessor;
use FOS\ElasticaBundle\Index\IndexManager;
use FOS\ElasticaBundle\Index\MappingBuilder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Oleg Andreyev <oleg.andreyev@intexsys.lv>
 */
class CreateCommand extends Command
{
    private $indexManager;
    private $mappingBuilder;
    private $configManager;
    private $aliasProcessor;

    public function __construct(
        IndexManager $indexManager,
        MappingBuilder $mappingBuilder,
        ConfigManager $configManager,
        AliasProcessor $aliasProcessor
    ) {
        parent::__construct();

        $this->indexManager = $indexManager;
        $this->mappingBuilder = $mappingBuilder;
        $this->configManager = $configManager;
        $this->aliasProcessor = $aliasProcessor;
    }

    protected function configure()
    {
        $this
            ->setName('fos:elastica:create')
            ->addOption('index', null, InputOption::VALUE_OPTIONAL, 'Index that needs to be created')
            ->addOption('no-alias', null, InputOption::VALUE_NONE, 'Do not alias index')
            ->setDescription('Creating empty index with mapping')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $indexes = (null !== $index = $input->getOption('index')) ? [$index] : \array_keys($this->indexManager->getAllIndexes());

        foreach ($indexes as $indexName) {
            $output->writeln(\sprintf('<info>Creating</info> <comment>%s</comment>', $indexName));

            $indexConfig = $this->configManager->getIndexConfiguration($indexName);
            $index = $this->indexManager->getIndex($indexName);
            if ($indexConfig->isUseAlias()) {
                $this->aliasProcessor->setRootName($indexConfig, $index);
            }
            $mapping = $this->mappingBuilder->buildIndexMapping($indexConfig);
            $index->create($mapping);

            if ($indexConfig->isUseAlias() && !$input->getOption('no-alias')) {
                $index->addAlias($indexName);
            }
        }

        return 0;
    }
}
