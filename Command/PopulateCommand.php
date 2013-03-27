<?php

namespace FOS\ElasticaBundle\Command;

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
            ->setName('fos:elastica:populate')
            ->setDescription('Populates search indexes from providers')
            ->addOption('no-reset', null, InputOption::VALUE_NONE, 'Do not reset the indexes before they are populated.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$input->getOption('no-reset')) {
            $output->writeln('Resetting indexes');
            $this->getContainer()->get('fos_elastica.reseter')->reset();
        }

        $output->writeln('Populating indexes');
        $this->getContainer()->get('fos_elastica.populator')->populate(function($text) use ($output) {
            $output->writeLn($text);
        });

        $output->writeln('Refreshing indexes');
		array_map(function($index) {
			$index->refresh();
		}, $this->getContainer()->get('fos_elastica.index_manager')->getAllIndexes());

        $output->writeln('Done');
    }
}
