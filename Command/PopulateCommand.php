<?php

namespace FOS\ElasticaBundle\Command;

use FOS\ElasticaBundle\Event\PopulateEvent;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\DialogHelper;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use FOS\ElasticaBundle\Index\IndexManager;
use FOS\ElasticaBundle\Provider\ProviderRegistry;
use FOS\ElasticaBundle\Provider\ProviderInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Populate the search index
 */
class PopulateCommand extends ContainerAwareCommand
{
    /**
     * @var IndexManager
     */
    private $indexManager;

    /**
     * @var ProviderRegistry
     */
    private $providerRegistry;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @see Symfony\Component\Console\Command\Command::configure()
     */
    protected function configure()
    {
        $this
            ->setName('fos:elastica:populate')
            ->addOption('index', null, InputOption::VALUE_OPTIONAL, 'The index to repopulate')
            ->addOption('type', null, InputOption::VALUE_OPTIONAL, 'The type to repopulate')
            ->addOption('no-reset', null, InputOption::VALUE_NONE, 'Do not reset index before populating')
            ->addOption('offset', null, InputOption::VALUE_REQUIRED, 'Start indexing at offset', 0)
            ->addOption('sleep', null, InputOption::VALUE_REQUIRED, 'Sleep time between persisting iterations (microseconds)', 0)
            ->addOption('batch-size', null, InputOption::VALUE_REQUIRED, 'Index packet size (overrides provider config option)')
            ->addOption('ignore-errors', null, InputOption::VALUE_NONE, 'Do not stop on errors')
            ->setDescription('Populates search indexes from providers')
        ;
    }

    /**
     * @see Symfony\Component\Console\Command\Command::initialize()
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->indexManager = $this->getContainer()->get('fos_elastica.index_manager');
        $this->providerRegistry = $this->getContainer()->get('fos_elastica.provider_registry');
        $this->eventDispatcher = $this->getContainer()->get('event_dispatcher');
    }

    /**
     * @see Symfony\Component\Console\Command\Command::execute()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $index         = $input->getOption('index');
        $type          = $input->getOption('type');
        $reset         = !$input->getOption('no-reset');
        $options       = $input->getOptions();

        $options['ignore-errors'] = $input->hasOption('ignore-errors');

        if ($input->isInteractive() && $reset && $input->getOption('offset')) {
            /** @var DialogHelper $dialog */
            $dialog = $this->getHelperSet()->get('dialog');
            if (!$dialog->askConfirmation($output, '<question>You chose to reset the index and start indexing with an offset. Do you really want to do that?</question>', true)) {
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
     * @param boolean         $reset
     * @param array           $options
     */
    private function populateIndex(OutputInterface $output, $index, $reset, $options)
    {
        /** @var $providers ProviderInterface[] */
        $providers = $this->providerRegistry->getIndexProviders($index);

        $this->populate($output, $providers, $index, null, $reset, $options);

        $output->writeln(sprintf('<info>Refreshing</info> <comment>%s</comment>', $index));
        $this->indexManager->getIndex($index)->refresh();
    }

    /**
     * Deletes/remaps an index type, populates it, and refreshes the index.
     *
     * @param OutputInterface $output
     * @param string          $index
     * @param string          $type
     * @param boolean         $reset
     * @param array           $options
     */
    private function populateIndexType(OutputInterface $output, $index, $type, $reset, $options)
    {
        $provider = $this->providerRegistry->getProvider($index, $type);

        $this->populate($output, array($type => $provider), $index, $type, $reset, $options);

        $output->writeln(sprintf('<info>Refreshing</info> <comment>%s</comment>', $index));
        $this->indexManager->getIndex($index)->refresh();
    }

    /**
     * @param OutputInterface     $output
     * @param ProviderInterface[] $providers
     * @param string              $index
     * @param string              $type
     * @param boolean             $reset
     * @param array               $options
     */
    private function populate(OutputInterface $output, array $providers, $index, $type, $reset, $options)
    {
        if ($reset) {
            if ($type) {
                $output->writeln(sprintf('<info>Resetting</info> <comment>%s/%s</comment>', $index, $type));
            } else {
                $output->writeln(sprintf('<info>Resetting</info> <comment>%s</comment>', $index));
            }
        }

        $this->eventDispatcher->dispatch(PopulateEvent::PRE_INDEX_POPULATE, new PopulateEvent($index, $type, $reset, $options));

        foreach ($providers as $providerType => $provider) {
            $event = new PopulateEvent($index, $providerType, $reset, $options);

            $this->eventDispatcher->dispatch(PopulateEvent::PRE_TYPE_POPULATE, $event);

            $loggerClosure = function($message) use ($output, $index, $providerType) {
                $output->writeln(sprintf('<info>Populating</info> %s/%s, %s', $index, $providerType, $message));
            };

            $provider->populate($loggerClosure, $options);

            $this->eventDispatcher->dispatch(PopulateEvent::POST_TYPE_POPULATE, $event);
        }

        $this->eventDispatcher->dispatch(PopulateEvent::POST_INDEX_POPULATE, new PopulateEvent($index, $type, $reset, $options));
    }
}
