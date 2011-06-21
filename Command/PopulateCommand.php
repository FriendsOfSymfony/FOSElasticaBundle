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
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setName('foq:elastica:populate')
            ->setDescription('Populates search indexes from providers');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Reseting indexes');
        $this->getContainer()->get('foq_elastica.reseter')->reset();

        $output->writeln('Setting mappings');
        $this->getContainer()->get('foq_elastica.mapping_registry')->applyMappings();

        $output->writeln('Populating indexes');
        $this->getContainer()->get('foq_elastica.populator')->populate(function($text) use ($output) {
            $output->writeLn($text);
        });

        $output->writeln('Done');
    }
}
