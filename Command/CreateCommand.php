<?php
/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Command;

use FOS\ElasticaBundle\Index\IndexManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Create command
 *
 * @author Oleg Andreyev <oleg.andreyev@intexsys.lv>
 */
class CreateCommand extends ContainerAwareCommand
{
    /**
     * @var IndexManager
     */
    private $indexManager;

    /**
     * @var MappingBuilder
     */
    private $mappingBuilder;

    /**
     * @var ConfigManager
     */
    private $configManager;

    /**
     * Alias processor
     *
     * @var AliasProcessor
     */
    private $aliasProcessor;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('fos:elastica:create')
            ->addOption('index', null, InputOption::VALUE_OPTIONAL, 'Index that needs to be created')
            ->setDescription('Creating empty index with mapping')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->indexManager = $this->getContainer()->get('fos_elastica.index_manager');
        $this->mappingBuilder = $this->getContainer()->get('fos_elastica.mapping_builder');
        $this->configManager = $this->getContainer()->get('fos_elastica.config_manager');
        $this->aliasProcessor = $this->getContainer()->get('fos_elastica.alias_processor');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $indexName = $input->getOption('index');
        $indexes = null === $indexName ? array_keys($this->indexManager->getAllIndexes()) : array($indexName);

        foreach ($indexes as $indexName) {
            $output->writeln(sprintf('<info>Creating</info> <comment>%s</comment>', $indexName));

            $indexConfig = $this->configManager->getIndexConfiguration($indexName);
            $index = $this->indexManager->getIndex($indexName);
            if ($indexConfig->isUseAlias()) {
                $this->aliasProcessor->setRootName($indexConfig, $index);
            }
            $mapping = $this->mappingBuilder->buildIndexMapping($indexConfig);
            $index->create($mapping, false);
        }
    }
}
