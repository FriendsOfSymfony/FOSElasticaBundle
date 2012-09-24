<?php

namespace FOQ\ElasticaBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;

/**
 * Populate the search index
 */
class PopulateCommand extends ContainerAwareCommand
{
    /**
     * @var FOQ\ElasticaBundle\IndexManager
     */
    private $indexManager;

    /**
     * @var FOQ\ElasticaBundle\Provider\ProviderRegistry
     */
    private $providerRegistry;

    /**
     * @var FOQ\ElasticaBundle\Resetter
     */
    private $resetter;

    /**
     * @see Symfony\Component\Console\Command\Command::configure()
     */
    protected function configure()
    {
        $this
            ->setName('foq:elastica:populate')
            ->addOption('index', null, InputOption::VALUE_OPTIONAL, 'The index to repopulate')
            ->addOption('type', null, InputOption::VALUE_OPTIONAL, 'The type to repopulate')
            ->addOption('no-reset', null, InputOption::VALUE_NONE, 'If set, the indexes will not been resetted before populating.')
            ->setDescription('Populates search indexes from providers')
        ;
    }

    /**
     * @see Symfony\Component\Console\Command\Command::initialize()
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->indexManager = $this->getContainer()->get('foq_elastica.index_manager');
        $this->providerRegistry = $this->getContainer()->get('foq_elastica.provider_registry');
        $this->resetter = $this->getContainer()->get('foq_elastica.resetter');
    }

    /**
     * @see Symfony\Component\Console\Command\Command::execute()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $index  = $input->getOption('index');
        $type   = $input->getOption('type');
        $reset  = $input->getOption('no-reset') ? false : true;

        if (null === $index && null !== $type) {
            throw new \InvalidArgumentException('Cannot specify type option without an index.');
        }

        if (null !== $index) {
            if (null !== $type) {
                $this->populateIndexType($output, $index, $type, $reset);
            } else {
                $this->populateIndex($output, $index, $reset);
            }
        } else {
            $indexes = array_keys($this->indexManager->getAllIndexes());

            foreach ($indexes as $index) {
                $this->populateIndex($output, $index, $reset);
            }
        }
    }

    /**
     * Recreates an index, populates its types, and refreshes the index.
     *
     * @param OutputInterface $output
     * @param string          $index
     * @param boolean         $reset
     */
    private function populateIndex(OutputInterface $output, $index, $reset)
    {
        if ($reset) {
            $output->writeln(sprintf('<info>Resetting</info> <comment>%s</comment>', $index));
            $this->resetter->resetIndex($index);
        }

        $providers = $this->providerRegistry->getIndexProviders($index);

        foreach ($providers as $type => $provider) {
            $loggerClosure = function($message) use ($output, $index, $type) {
                $output->writeln(sprintf('<info>Populating</info> %s/%s, %s', $index, $type, $message));
            };

            $provider->populate($loggerClosure);
        }

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
     */
    private function populateIndexType(OutputInterface $output, $index, $type, $reset)
    {
        if ($reset) {
            $output->writeln(sprintf('Resetting: %s/%s', $index, $type));
            $this->resetter->resetIndexType($index, $type);
        }

        $loggerClosure = function($message) use ($output, $index, $type) {
            $output->writeln(sprintf('Populating: %s/%s, %s', $index, $type, $message));
        };

        $provider = $this->providerRegistry->getProvider($index, $type);
        $provider->populate($loggerClosure);

        $output->writeln(sprintf('Refreshing: %s', $index));
        $this->indexManager->getIndex($index)->refresh();
    }
}
