<?php

namespace FOS\ElasticaBundle\Command;

use Elastica\Search;
use Elastica\Type;
use FOS\ElasticaBundle\Elastica\Index;
// use FOS\ElasticaBundle\Event\IndexPopulateEvent;
// use FOS\ElasticaBundle\Event\TypePopulateEvent;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Reindex with zero downtime
 * @see https://www.elastic.co/blog/changing-mapping-with-zero-downtime#_reindexing_your_data_with_zero_downtime
 * @see https://www.elastic.co/guide/en/elasticsearch/guide/current/reindex.html
 */
class ReindexCommand extends ContainerAwareCommand
{
    /**
     * @var \FOS\ElasticaBundle\Configuration\ConfigManager
     */
    private $configManager;

    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var \FOS\ElasticaBundle\IndexManager
     */
    private $indexManager;

    /**
     * @var ProgressClosureBuilder
     */
    private $progressClosureBuilder;

    /**
     * @var \FOS\ElasticaBundle\Resetter
     */
    private $resetter;

    /**
     * @see Symfony\Component\Console\Command\Command::configure()
     */
    protected function configure()
    {
        $this
            ->setName('fos:elastica:reindex')
            ->addOption('index', null, InputOption::VALUE_REQUIRED, 'The index to reindex')
            ->addOption('expiry-time', null, InputOption::VALUE_REQUIRED, 'How long each batch of documents has to process', '1m')
            ->addOption('size-per-shard', null, InputOption::VALUE_REQUIRED, 'Maximum number of documents each shard may contribute to a batch', 1000)
            ->addOption('ignore-errors', null, InputOption::VALUE_NONE, 'Do not stop on errors')
            ->addOption('no-overwrite-format', null, InputOption::VALUE_NONE, 'Prevent this command from overwriting ProgressBar\'s formats')
            ->addOption('no-post-populate', null, InputOption::VALUE_NONE, 'Prevent this command from switching the index alias on completion')
            ->setDescription('Reindexes a search index')
        ;
    }

    /**
     * {@inheritDoc}
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->configManager = $this->getContainer()->get('fos_elastica.config_manager');
        $this->dispatcher = $this->getContainer()->get('event_dispatcher');
        $this->indexManager = $this->getContainer()->get('fos_elastica.index_manager');
        $this->resetter = $this->getContainer()->get('fos_elastica.resetter');
        $this->progressClosureBuilder = new ProgressClosureBuilder();

        if (!$input->getOption('no-overwrite-format') && class_exists('Symfony\\Component\\Console\\Helper\\ProgressBar')) {
            ProgressBar::setFormatDefinition('normal', " %current%/%max% [%bar%] %percent:3s%%\n%message%");
            ProgressBar::setFormatDefinition('verbose', " %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%\n%message%");
            ProgressBar::setFormatDefinition('very_verbose', " %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s%\n%message%");
            ProgressBar::setFormatDefinition('debug', " %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s%\n%message%");
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $indexName = $input->getOption('index');
        if (!$indexName) {
            throw new \InvalidArgumentException('Must specify index option.');
        }

        $indexConfig = $this->configManager->getIndexConfiguration($indexName);
        if (!$indexConfig->isUseAlias()) {
            throw new \InvalidArgumentException('Specified index not configured to use alias.');
        }

        $options = $input->getOptions();

        $this->reindexIndex($output, $indexName, $options);
    }

    private function reindexIndex(OutputInterface $output, $indexName, $options)
    {
        $output->writeln(sprintf('<info>Reindexing</info> <comment>%s</comment>', $indexName));
        $this->resetter->resetIndex($indexName, true);

        // $event = new IndexPopulateEvent($indexName, $reset, $options);
        // $this->dispatcher->dispatch(IndexPopulateEvent::PRE_INDEX_POPULATE, $event);

        $index = $this->indexManager->getIndex($indexName);
        $typeNames = array_keys($index->getMapping());
        foreach ($typeNames as $typeName) {
            $type = $index->getType($typeName);
            $this->reindexType($output, $type, $options);
        }

        // $this->dispatcher->dispatch(IndexPopulateEvent::POST_INDEX_POPULATE, $event);

        $this->refreshIndex($output, $indexName, !$options['no-post-populate']);
    }

    private function reindexType(OutputInterface $output, Type $type, $options)
    {
        // $event = new TypePopulateEvent($index, $type, $reset, $options);
        // $this->dispatcher->dispatch(TypePopulateEvent::PRE_TYPE_POPULATE, $event);

        $search = new Search($type->getIndex()->getClient());
        $search->addIndex($type->getIndex()->getOriginalName());
        $search->addType($type);

        $nbObjects = $search->count();
        $loggerClosure = $this->progressClosureBuilder->build($output, 'Reindexing', $type->getIndex()->getOriginalName(), $type->getName());
        $scanAndScroll = $search->scanAndScroll($options['expiry-time'], $options['size-per-shard']);

        try {
            $scanAndScroll->rewind();
        } catch (\Exception $e) {
            $output->writeln("Exception trying to initiate scan: " . $e->getMessage());
            $output->writeln($e->getTraceAsString());
            throw $e;
        }

        $type->setSerializer(function($object) { return $object->getData(); });

        $output->writeln(sprintf("<info>Reading</info> <comment>from %s/%s</comment>", $type->getIndex()->getOriginalName(), $type->getName()));
        $output->writeln(sprintf("<info>Writing</info> <comment>to %s/%s</comment>", $type->getIndex()->getName(), $type->getName()));
        while ($scanAndScroll->valid()) {
            $resultSet = $scanAndScroll->current();
            $resultCount = $resultSet->count();

            try {
                $type->addObjects($resultSet->getResults());
            } catch (\Exception $e) {
                if ($output->getVerbosity() >= OutputInterface::VERBOSITY_DEBUG) {
                    $output->writeln($e->getTraceAsString());
                }

                if (!$options['ignore-errors']) {
                    throw $e;
                }

                if (null !== $loggerClosure) {
                    $loggerClosure(
                        $resultCount,
                        $nbObjects,
                        sprintf('<error>%s</error>', $e->getMessage())
                    );
                }
            }

            if (null !== $loggerClosure) {
                $loggerClosure($resultCount, $nbObjects);
            }

            // Get the next batch
            try {
                $scanAndScroll->next();
            } catch (\Exception $e) {
                if ($output->getVerbosity() >= OutputInterface::VERBOSITY_DEBUG) {
                    $output->writeln($e->getTraceAsString());
                }

                throw $e;
            }
        }

        // $this->dispatcher->dispatch(TypePopulateEvent::POST_TYPE_POPULATE, $event);
    }

    private function refreshIndex(OutputInterface $output, $index, $postPopulate = true)
    {
        if ($postPopulate) {
            $this->resetter->postPopulate($index);
        }

        $output->writeln(sprintf('<info>Refreshing</info> <comment>%s</comment>', $index));
        $this->indexManager->getIndex($index)->refresh();
    }
}
