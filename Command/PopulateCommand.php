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
        $output->writeln('Resetting indexes');
        $this->getContainer()->get('foq_elastica.resetter')->reset();

        $output->writeln('Populating indexes');
        $this->getContainer()->get('foq_elastica.populator')->populate(function($text) use ($output) {
            $output->writeLn($text);
        });

        $output->writeln('Refreshing indexes');
		array_map(function($index) {
			$index->refresh();
		}, $this->getContainer()->get('foq_elastica.index_manager')->getAllIndexes());

        $output->writeln('Done');
    }
}
