<?php

namespace FOS\ElasticaBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use FOS\ElasticaBundle\IndexManager;
use FOS\ElasticaBundle\Resetter;

/**
 * Purge search indexes
 */
class PurgeCommand extends ContainerAwareCommand
{
    /**
     * @var IndexManager
     */
    private $indexManager;

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
            ->setName('fos:elastica:purge')
            ->addOption('index', null, InputOption::VALUE_OPTIONAL, 'The index to purge')
            ->addOption('type', null, InputOption::VALUE_OPTIONAL, 'The type to purge')
            ->setDescription('Purge search indexes')
        ;
    }

    /**
     * @see Symfony\Component\Console\Command\Command::initialize()
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->indexManager = $this->getContainer()->get('fos_elastica.index_manager');
        $this->resetter = $this->getContainer()->get('fos_elastica.resetter');
    }

    /**
     * @see Symfony\Component\Console\Command\Command::execute()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $index  = $input->getOption('index');
        $type   = $input->getOption('type');

        if (null === $index && null !== $type) {
            throw new \InvalidArgumentException('Cannot specify type option without an index.');
        }

        if (null !== $type) {
            $output->writeln(sprintf('Resetting: %s/%s', $index, $type));
            $this->resetter->resetIndex($index, $type);
        } else {
            $indexes = null === $index
                ? array_keys($this->indexManager->getAllIndexes())
                : array($index)
            ;

            foreach ($indexes as $index) {
                $output->writeln(sprintf('<info>Resetting</info> <comment>%s</comment>', $index));
                $this->resetter->resetIndex($index);
            }
        }
    }
}
