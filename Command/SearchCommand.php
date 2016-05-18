<?php

namespace FOS\ElasticaBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Elastica\Query;
use Elastica\Result;

/**
 * Searches a type.
 */
class SearchCommand extends ContainerAwareCommand
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setName('fos:elastica:search')
            ->addArgument('type', InputArgument::REQUIRED, 'The type to search in')
            ->addArgument('query', InputArgument::REQUIRED, 'The text to search')
            ->addOption('index', null, InputOption::VALUE_REQUIRED, 'The index to search in')
            ->addOption('limit', null, InputOption::VALUE_REQUIRED, 'The maximum number of documents to return', 20)
            ->addOption('show-field', null, InputOption::VALUE_REQUIRED, 'Field to show, null uses the first field')
            ->addOption('show-source', null, InputOption::VALUE_NONE, 'Show the documents sources')
            ->addOption('show-id', null, InputOption::VALUE_NONE, 'Show the documents ids')
            ->addOption('explain', null, InputOption::VALUE_NONE, 'Enables explanation for each hit on how its score was computed.')
            ->setDescription('Searches documents in a given type and index')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $indexName = $input->getOption('index');
        /** @var $index \Elastica\Index */
        $index = $this->getContainer()->get('fos_elastica.index_manager')->getIndex($indexName ? $indexName : null);
        $type  = $index->getType($input->getArgument('type'));
        $query = Query::create($input->getArgument('query'));
        $query->setSize($input->getOption('limit'));
        if ($input->getOption('explain')) {
            $query->setExplain(true);
        }

        $resultSet = $type->search($query);

        $output->writeLn(sprintf('Found %d results', $type->count($query)));
        foreach ($resultSet->getResults() as $result) {
            $output->writeLn($this->formatResult($result, $input->getOption('show-field'), $input->getOption('show-source'), $input->getOption('show-id'), $input->getOption('explain')));
        }
    }

    /**
     * @param Result $result
     * @param string $showField
     * @param string $showSource
     * @param string $showId
     * @param string $explain
     *
     * @return string
     */
    protected function formatResult(Result $result, $showField, $showSource, $showId, $explain)
    {
        $source = $result->getSource();
        if ($showField) {
            $toString = isset($source[$showField]) ? $source[$showField] : '-';
        } else {
            $toString = reset($source);
        }
        $string = sprintf('[%0.2f] %s', $result->getScore(), var_export($toString, true));
        if ($showSource) {
            $string = sprintf('%s %s', $string, json_encode($source));
        }
        if ($showId) {
            $string = sprintf('{%s} %s', $result->getId(), $string);
        }
        if ($explain) {
            $string = sprintf('%s %s', $string, json_encode($result->getExplanation()));
        }

        return $string;
    }
}
