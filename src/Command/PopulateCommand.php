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

use Elastica\Exception\Bulk\ResponseException as BulkResponseException;
use FOS\ElasticaBundle\Event\IndexPopulateEvent;
use FOS\ElasticaBundle\Event\TypePopulateEvent;
use FOS\ElasticaBundle\Index\IndexManager;
use FOS\ElasticaBundle\Index\Resetter;
use FOS\ElasticaBundle\Persister\Event\Events;
use FOS\ElasticaBundle\Persister\Event\OnExceptionEvent;
use FOS\ElasticaBundle\Persister\Event\PostAsyncInsertObjectsEvent;
use FOS\ElasticaBundle\Persister\Event\PostInsertObjectsEvent;
use FOS\ElasticaBundle\Persister\InPlacePagerPersister;
use FOS\ElasticaBundle\Persister\PagerPersisterInterface;
use FOS\ElasticaBundle\Persister\PagerPersisterRegistry;
use FOS\ElasticaBundle\Provider\PagerProviderRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\LegacyEventDispatcherProxy;

/**
 * Populate the search index.
 */
class PopulateCommand extends Command
{
    protected static $defaultName = 'fos:elastica:populate';

    /**
     * @var EventDispatcherInterface 
     */
    private $dispatcher;

    /**
     * @var IndexManager 
     */
    private $indexManager;

    /**
     * @var PagerProviderRegistry
     */
    private $pagerProviderRegistry;

    /**
     * @var PagerPersisterRegistry
     */
    private $pagerPersisterRegistry;

    /**
     * @var PagerPersisterInterface
     */
    private $pagerPersister;

    /**
     * @var Resetter
     */
    private $resetter;

    public function __construct(
        EventDispatcherInterface $dispatcher,
        IndexManager $indexManager,
        PagerProviderRegistry $pagerProviderRegistry,
        PagerPersisterRegistry $pagerPersisterRegistry,
        Resetter $resetter
    ) {
        parent::__construct();

        $this->dispatcher = $dispatcher;

        if (class_exists(LegacyEventDispatcherProxy::class)) {
            $this->dispatcher = LegacyEventDispatcherProxy::decorate($dispatcher);
        }

        $this->indexManager = $indexManager;
        $this->pagerProviderRegistry = $pagerProviderRegistry;
        $this->pagerPersisterRegistry = $pagerPersisterRegistry;
        $this->resetter = $resetter;
    }

    protected function configure()
    {
        $this
            ->setName('fos:elastica:populate')
            ->addOption('index', null, InputOption::VALUE_OPTIONAL, 'The index to repopulate')
            ->addOption('type', null, InputOption::VALUE_OPTIONAL, 'The type to repopulate')
            ->addOption('no-reset', null, InputOption::VALUE_NONE, 'Do not reset index before populating')
            ->addOption('no-delete', null, InputOption::VALUE_NONE, 'Do not delete index after populate')
            ->addOption('sleep', null, InputOption::VALUE_REQUIRED, 'Sleep time between persisting iterations (microseconds)', 0)
            ->addOption('ignore-errors', null, InputOption::VALUE_NONE, 'Do not stop on errors')
            ->addOption('no-overwrite-format', null, InputOption::VALUE_NONE, 'Prevent this command from overwriting ProgressBar\'s formats')

            ->addOption('first-page', null, InputOption::VALUE_REQUIRED, 'The pager\'s page to start population from. Including the given page.', 1)
            ->addOption('last-page', null, InputOption::VALUE_REQUIRED, 'The pager\'s page to end population on. Including the given page.', null)
            ->addOption('max-per-page', null, InputOption::VALUE_REQUIRED, 'The pager\'s page size', 100)
            ->addOption('pager-persister', null, InputOption::VALUE_REQUIRED, 'The pager persister to be used to populate the index', InPlacePagerPersister::NAME)

            ->setDescription('Populates search indexes from providers')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->pagerPersister = $this->pagerPersisterRegistry->getPagerPersister($input->getOption('pager-persister'));

        if (!$input->getOption('no-overwrite-format')) {
            ProgressBar::setFormatDefinition('normal', " %current%/%max% [%bar%] %percent:3s%%\n%message%");
            ProgressBar::setFormatDefinition('verbose', " %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%\n%message%");
            ProgressBar::setFormatDefinition('very_verbose', " %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s%\n%message%");
            ProgressBar::setFormatDefinition('debug', " %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s%\n%message%");
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $index = $input->getOption('index');
        $type = $input->getOption('type');
        $reset = !$input->getOption('no-reset');
        $delete = !$input->getOption('no-delete');

        $options = [
            'delete' => $delete,
            'reset' => $reset,
            'ignore_errors' => $input->getOption('ignore-errors'),
            'sleep' => $input->getOption('sleep'),
            'first_page' => $input->getOption('first-page'),
            'max_per_page' => $input->getOption('max-per-page'),
        ];

        if ($input->getOption('last-page')) {
            $options['last_page'] = $input->getOption('last-page');
        }

        if ($input->isInteractive() && $reset && 1 < $options['first_page']) {
            /** @var QuestionHelper $dialog */
            $dialog = $this->getHelperSet()->get('question');
            if (!$dialog->ask($input, $output, new Question('<question>You chose to reset the index and start indexing with an offset. Do you really want to do that?</question>'))) {
                return;
            }
        }

        if (null === $index && null !== $type) {
            throw new \InvalidArgumentException('Cannot specify type option without an index.');
        }

        if (null !== $index) {
            if (null !== $type) {
                $this->populateIndexType($output, $index, $type, $reset, $options);
            } else {
                $this->populateIndex($output, $index, $reset, $options);
            }
        } else {
            $indexes = array_keys($this->indexManager->getAllIndexes());

            foreach ($indexes as $index) {
                $this->populateIndex($output, $index, $reset, $options);
            }
        }
    }

    /**
     * Recreates an index, populates its types, and refreshes the index.
     *
     * @param OutputInterface $output
     * @param string          $index
     * @param bool            $reset
     * @param array           $options
     */
    private function populateIndex(OutputInterface $output, $index, $reset, $options)
    {
        $event = new IndexPopulateEvent($index, $reset, $options);
        $this->dispatcher->dispatch(IndexPopulateEvent::PRE_INDEX_POPULATE, $event);

        if ($event->isReset()) {
            $output->writeln(sprintf('<info>Resetting</info> <comment>%s</comment>', $index));
            $this->resetter->resetIndex($index, true);
        }

        $types = array_keys($this->pagerProviderRegistry->getIndexProviders($index));
        foreach ($types as $type) {
            $this->populateIndexType($output, $index, $type, false, $event->getOptions());
        }

        $this->dispatcher->dispatch(IndexPopulateEvent::POST_INDEX_POPULATE, $event);

        $this->refreshIndex($output, $index);
    }

    /**
     * Deletes/remaps an index type, populates it, and refreshes the index.
     *
     * @param OutputInterface $output
     * @param string          $index
     * @param string          $type
     * @param bool            $reset
     * @param array           $options
     */
    private function populateIndexType(OutputInterface $output, $index, $type, $reset, $options)
    {
        $event = new TypePopulateEvent($index, $type, $reset, $options);
        $this->dispatcher->dispatch(TypePopulateEvent::PRE_TYPE_POPULATE, $event);

        if ($event->isReset()) {
            $output->writeln(sprintf('<info>Resetting</info> <comment>%s/%s</comment>', $index, $type));
            $this->resetter->resetIndexType($index, $type);
        }

        $offset = 1 < $options['first_page'] ? ($options['first_page'] - 1) * $options['max_per_page'] : 0;
        $loggerClosure = ProgressClosureBuilder::build($output, 'Populating', $index, $type, $offset);

        $this->dispatcher->addListener(
            Events::ON_EXCEPTION,
            function(OnExceptionEvent $event) use ($loggerClosure) {
                $loggerClosure(
                    count($event->getObjects()),
                    $event->getPager()->getNbResults(),
                    sprintf('<error>%s</error>', $event->getException()->getMessage())
                );
            }
        );

        $this->dispatcher->addListener(
            Events::POST_INSERT_OBJECTS,
            function(PostInsertObjectsEvent $event) use ($loggerClosure) {
                $loggerClosure(count($event->getObjects()), $event->getPager()->getNbResults());
            }
        );

        $this->dispatcher->addListener(
            Events::POST_ASYNC_INSERT_OBJECTS,
            function(PostAsyncInsertObjectsEvent $event) use ($loggerClosure) {
                $loggerClosure($event->getObjectsCount(), $event->getPager()->getNbResults(), $event->getErrorMessage());
            }
        );

        if ($options['ignore_errors']) {
            $this->dispatcher->addListener(Events::ON_EXCEPTION, function(OnExceptionEvent $event) {
                if ($event->getException() instanceof BulkResponseException) {
                    $event->setIgnore(true);
                }
            });
        }

        $provider = $this->pagerProviderRegistry->getProvider($index, $type);

        $pager = $provider->provide($options);

        $options['indexName'] = $index;
        $options['typeName'] = $type;

        $this->pagerPersister->insert($pager, $options);

        $this->dispatcher->dispatch(TypePopulateEvent::POST_TYPE_POPULATE, $event);

        $this->refreshIndex($output, $index);
    }

    /**
     * Refreshes an index.
     *
     * @param OutputInterface $output
     * @param string          $index
     */
    private function refreshIndex(OutputInterface $output, $index)
    {
        $output->writeln(sprintf('<info>Refreshing</info> <comment>%s</comment>', $index));
        $this->indexManager->getIndex($index)->refresh();
    }
}
