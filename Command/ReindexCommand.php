<?php

namespace FOS\ElasticaBundle\Command;

use Elastica\Query;
use Elastica\Search;
use Elastica\Type;
use FOS\ElasticaBundle\Elastica\Index;
use FOS\ElasticaBundle\Event\IndexPopulateEvent;
use FOS\ElasticaBundle\Event\TypePopulateEvent;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\DialogHelper;
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
            ->addOption('no-reset', null, InputOption::VALUE_NONE, 'Do not reset index before populating')
            ->addOption('offset', null, InputOption::VALUE_REQUIRED, 'Start indexing at offset', 0)
            ->addOption('sleep', null, InputOption::VALUE_REQUIRED, 'Sleep time between persisting iterations (microseconds)', 0)
            ->addOption('batch-size', null, InputOption::VALUE_REQUIRED, 'Index packet size (overrides provider config option)')
            ->addOption('ignore-errors', null, InputOption::VALUE_NONE, 'Do not stop on errors')
            ->addOption('no-overwrite-format', null, InputOption::VALUE_NONE, 'Prevent this command from overwriting ProgressBar\'s formats')
            ->addOption('no-post-populate', null, InputOption::VALUE_NONE, 'Prevent this command from overwriting ProgressBar\'s formats')
            ->setDescription('Populates search indexes from providers')
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
        $options =  $input->getOptions();

        if (!$indexName) {
            throw new \InvalidArgumentException('Must specify index option.');
        }

        $indexConfig = $this->configManager->getIndexConfiguration($indexName);
        if (!$indexConfig->isUseAlias()) {
            throw new \InvalidArgumentException('Specified index not configured to use alias.');
        }

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

        // $numShards = count($this->getContainer()->getParameter('elasticsearch.servers'));
        $numShards = 1;
        $sizePerShard = 10;
        $scanAndScroll = $search->scanAndScroll($numShards . 'm', $sizePerShard);

        try {
            $scanAndScroll->rewind();
        } catch (\Exception $e) {
            $output->writeln("Exception trying to initiate scan: " . $e->getMessage());
            $output->writeln($e->getTraceAsString());
            throw $e;
        }

        $total = 0;
        $type->setSerializer(function($object) { return $object->getData(); });

        $output->writeln("Looping through " . $type->getIndex()->getOriginalName() . "/" . $type->getName() . " results...");
        while ($scanAndScroll->valid()) {
            $resultSet = $scanAndScroll->current();

            $output->writeln("Writing " . $resultSet->count() . " results to " . $type->getIndex()->getName() . "/" . $type->getName());
            try {
                $type->addObjects($resultSet->getResults());
            } catch (\Exception $e) {
                $output->writeln("Exception trying to add documents: " . $e->getMessage());
                $output->writeln($e->getTraceAsString());
                throw $e;
            }

            // Get the next batch
            try {
                $scanAndScroll->next();
            } catch (\Exception $e) {
                $output->writeln("Exception trying to perform next scroll search: " . $e->getMessage());
                $output->writeln($e->getTraceAsString());
                exit;
            }
        }

        $output->writeln("Wrote $total total results to " . $type->getIndex()->getName() . "/" . $type->getName());

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
