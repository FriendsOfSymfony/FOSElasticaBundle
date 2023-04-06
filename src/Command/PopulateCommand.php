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

use Elastica\Exception\Bulk\ResponseException as BulkResponseException;
use FOS\ElasticaBundle\Event\AbstractIndexPopulateEvent;
use FOS\ElasticaBundle\Event\PostIndexPopulateEvent;
use FOS\ElasticaBundle\Event\PreIndexPopulateEvent;
use FOS\ElasticaBundle\Index\IndexManager;
use FOS\ElasticaBundle\Index\Resetter;
use FOS\ElasticaBundle\Persister\Event\OnExceptionEvent;
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

/**
 * Populate the search index.
 *
 * @phpstan-import-type TOptions from AbstractIndexPopulateEvent
 */
class PopulateCommand extends Command
{
    private EventDispatcherInterface $dispatcher;
    private IndexManager $indexManager;
    private PagerProviderRegistry $pagerProviderRegistry;
    private PagerPersisterRegistry $pagerPersisterRegistry;
    private PagerPersisterInterface $pagerPersister;
    private Resetter $resetter;

    public function __construct(
        EventDispatcherInterface $dispatcher,
        IndexManager $indexManager,
        PagerProviderRegistry $pagerProviderRegistry,
        PagerPersisterRegistry $pagerPersisterRegistry,
        Resetter $resetter
    ) {
        parent::__construct();

        $this->dispatcher = $dispatcher;
        $this->indexManager = $indexManager;
        $this->pagerProviderRegistry = $pagerProviderRegistry;
        $this->pagerPersisterRegistry = $pagerPersisterRegistry;
        $this->resetter = $resetter;
    }

    /**
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('fos:elastica:populate')
            ->addOption('index', null, InputOption::VALUE_OPTIONAL, 'The index to repopulate')
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

    /**
     * @return void
     */
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

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $indexes = (null !== $index = $input->getOption('index')) ? [$index] : \array_keys($this->indexManager->getAllIndexes());
        $reset = !$input->getOption('no-reset');
        $delete = !$input->getOption('no-delete');

        /** @var TOptions $options */
        $options = [
            'delete' => $delete,
            'reset' => $reset,
            'ignore_errors' => $input->getOption('ignore-errors'),
            'sleep' => $input->getOption('sleep'),
            'first_page' => $input->getOption('first-page'),
            'max_per_page' => $input->getOption('max-per-page'),
            'pager_persister' => $input->getOption('pager-persister'),
        ];

        if ($input->getOption('last-page')) {
            $options['last_page'] = $input->getOption('last-page');
        }

        if ($input->isInteractive() && $reset && 1 < $options['first_page']) {
            /** @var QuestionHelper $dialog */
            $dialog = $this->getHelperSet()->get('question');
            if (!$dialog->ask($input, $output, new Question('<question>You chose to reset the index and start indexing with an offset. Do you really want to do that?</question>'))) {
                return 1;
            }
        }

        foreach ($indexes as $index) {
            $this->populateIndex($output, $index, $reset, $options);
        }

        return 0;
    }

    /**
     * Recreates an index, populates it, and refreshes it.
     *
     * @phpstan-param TOptions $options
     */
    private function populateIndex(OutputInterface $output, string $index, bool $reset, array $options): void
    {
        $this->dispatcher->dispatch($event = new PreIndexPopulateEvent($index, $reset, $options));

        if ($reset = $event->isReset()) {
            $output->writeln(\sprintf('<info>Resetting</info> <comment>%s</comment>', $index));
            $this->resetter->resetIndex($index, true);
        }

        $offset = 1 < $options['first_page'] ? ($options['first_page'] - 1) * $options['max_per_page'] : 0;
        $consoleLogger = new ConsoleProgressLogger($output, 'Populating', $index, $offset);

        $this->dispatcher->addListener(
            OnExceptionEvent::class,
            $exceptionListener = function (OnExceptionEvent $event) use ($consoleLogger) {
                $consoleLogger->call(
                    \count($event->getObjects()),
                    0,
                    $event->getPager()->getNbResults(),
                    \sprintf('<error>%s</error>', $event->getException()->getMessage())
                );
            }
        );

        $this->dispatcher->addListener(
            PostInsertObjectsEvent::class,
            $postInsertListener = function (PostInsertObjectsEvent $event) use ($consoleLogger) {
                $consoleLogger->call(\count($event->getObjects()), $event->getFilteredObjectCount(), $event->getPager()->getNbResults());
            }
        );

        if ($options['ignore_errors']) {
            $this->dispatcher->addListener(
                OnExceptionEvent::class,
                $ignoreExceptionsListener = function (OnExceptionEvent $event) {
                    if ($event->getException() instanceof BulkResponseException) {
                        $event->setIgnored(true);
                    }
                }
            );
        }

        $provider = $this->pagerProviderRegistry->getProvider($index);
        $pager = $provider->provide($options);

        $this->pagerPersister->insert($pager, \array_merge($options, ['indexName' => $index]));

        $consoleLogger->finish();
        $this->dispatcher->removeListener(OnExceptionEvent::class, $exceptionListener);
        $this->dispatcher->removeListener(PostInsertObjectsEvent::class, $postInsertListener);
        if (isset($ignoreExceptionsListener)) {
            $this->dispatcher->removeListener(OnExceptionEvent::class, $ignoreExceptionsListener);
        }

        $this->dispatcher->dispatch(new PostIndexPopulateEvent($index, $reset, $options));

        $this->refreshIndex($output, $index);
    }

    /**
     * Refreshes an index.
     */
    private function refreshIndex(OutputInterface $output, string $index): void
    {
        $output->writeln(\sprintf('<info>Refreshing</info> <comment>%s</comment>', $index));
        $this->indexManager->getIndex($index)->refresh();
        $output->writeln('');
    }
}
