<?php

namespace FOQ\ElasticaBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use Elastica_Query;
use Elastica_Result;

/**
 * Searches a type
 */
class SearchCommand extends Command
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setDefinition(array(
                new InputArgument('type', InputArgument::REQUIRED, 'The type to search in'),
                new InputArgument('query', InputArgument::REQUIRED, 'The text to search'),
            ))
            ->addOption('index', null, InputOption::VALUE_NONE, 'The index to search in')
            ->addOption('limit', null, InputOption::VALUE_REQUIRED, 'The maximum number of documents to return', 20)
            ->addOption('show-source', null, InputOption::VALUE_NONE, 'Show the documents sources')
            ->setName('foq:elastica:search')
            ->setDescription('Searches documents in a given type and index');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $index = $this->container->get('foq_elastica.index_manager')->getIndex($input->getOption('index'));
        $type  = $index->getType($input->getArgument('type'));
        $query = Elastica_Query::create($input->getArgument('query'));
        $query->setLimit($input->getOption('limit'));

        $resultSet = $type->search($query);

        $output->writeLn(sprintf('Found %d results', $type->count($query)));
        foreach ($resultSet->getResults() as $result) {
            $output->writeLn($this->formatResult($result, $input->getOption('show-source')));
        }
    }

    protected function formatResult(Elastica_Result $result, $showSource)
    {
        $source = $result->getSource();
        if ($showSource) {
            $string = sprintf('[%0.2f] %s %s', $result->getScore(), var_export(reset($source), true), json_encode($source));
        } else {
            $string = sprintf('[%0.2f] %s', $result->getScore(), var_export(reset($source), true));
        }

        return $string;
    }
}
