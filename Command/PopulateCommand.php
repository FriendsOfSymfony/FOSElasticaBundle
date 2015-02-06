<?php

namespace FOS\ElasticaBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\DialogHelper;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use FOS\ElasticaBundle\IndexManager;
use FOS\ElasticaBundle\Provider\ProviderRegistry;
use FOS\ElasticaBundle\Resetter;
use FOS\ElasticaBundle\Provider\ProviderInterface;
use Symfony\Component\Console\Helper\ProgressBar;

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
     * @var Resetter
     */
    private $resetter;

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
        $this->resetter = $this->getContainer()->get('fos_elastica.resetter');
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
     * @param ProviderInterface $provider
     * @param OutputInterface $output
     * @param string $input
     * @param string $type
     * @param array $options
     */
    private function doPopulateType(ProviderInterface $provider, OutputInterface $output, $input, $type, $options)
    {
        $loggerClosure = $this->getLoggerClosure($output, $input, $type);

        $provider->populate($loggerClosure, $options);
    }

    /**
     * Builds a loggerClosure to be called from inside the Provider to update the command
     * line.
     *
     * @param OutputInterface $output
     * @param string $index
     * @param string $type
     * @return callable
     */
    private function getLoggerClosure(OutputInterface $output, $index, $type)
    {
        if (!class_exists('Symfony\Component\Console\Helper\ProgressBar')) {
            $lastStep = null;
            $current = 0;

            return function ($increment, $totalObjects) use ($output, $index, $type, &$lastStep, &$current) {
                if ($current + $increment > $totalObjects) {
                    $increment = $totalObjects - $current;
                }

                $currentTime = microtime(true);
                $timeDifference = $currentTime - $lastStep;
                $objectsPerSecond = $lastStep ? ($increment / $timeDifference) : $increment;
                $lastStep = $currentTime;
                $current += $increment;
                $percent = 100 * $current / $totalObjects;

                $output->writeln(sprintf(
                    '<info>Populating</info> <comment>%s/%s</comment> %0.1f%% (%d/%d), %d objects/s (RAM: current=%uMo peak=%uMo)',
                    $index,
                    $type,
                    $percent,
                    $current,
                    $totalObjects,
                    $objectsPerSecond,
                    round(memory_get_usage() / (1024 * 1024)),
                    round(memory_get_peak_usage() / (1024 * 1024))
                ));
            };
        }

        ProgressBar::setFormatDefinition('normal', " %current%/%max% [%bar%] %percent:3s%%\n%message%");
        ProgressBar::setFormatDefinition('verbose', " %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%\n%message%");
        ProgressBar::setFormatDefinition('very_verbose', " %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s%\n%message%");
        ProgressBar::setFormatDefinition('debug', " %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s%\n%message%");
        $progress = null;

        return function ($increment, $totalObjects) use (&$progress, $output, $index, $type) {
            if (null === $progress) {
                $progress = new ProgressBar($output, $totalObjects);
                $progress->start();
            }

            $progress->setMessage(sprintf('<info>Populating</info> <comment>%s/%s</comment>', $index, $type));
            $progress->advance($increment);

            if ($progress->getProgress() >= $progress->getMaxSteps()) {
                $progress->finish();
            }
        };
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
        if ($reset) {
            $output->writeln(sprintf('<info>Resetting</info> <comment>%s</comment>', $index));
            $this->resetter->resetIndex($index, true);
        }

        /** @var $providers ProviderInterface[] */
        $providers = $this->providerRegistry->getIndexProviders($index);

        foreach ($providers as $type => $provider) {
            $this->doPopulateType($provider, $output, $index, $type, $options);
        }

        $this->refreshIndex($output, $index);
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
        if ($reset) {
            $output->writeln(sprintf('<info>Resetting</info> <comment>%s/%s</comment>', $index, $type));
            $this->resetter->resetIndexType($index, $type);
        }

        $provider = $this->providerRegistry->getProvider($index, $type);
        $this->doPopulateType($provider, $output, $index, $type, $options);

        $this->refreshIndex($output, $index, false);
    }

    /**
     * Refreshes an index.
     *
     * @param OutputInterface $output
     * @param string $index
     * @param bool $postPopulate
     */
    private function refreshIndex(OutputInterface $output, $index, $postPopulate = true)
    {
        if ($postPopulate) {
            $this->resetter->postPopulate($index);
        }

        $output->writeln(sprintf('<info>Refreshing</info> <comment>%s</comment>', $index));
        $this->indexManager->getIndex($index)->refresh();
    }
}
