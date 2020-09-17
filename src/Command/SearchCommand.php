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

use Elastica\Query;
use Elastica\Result;
use FOS\ElasticaBundle\Index\IndexManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Searches a type.
 */
class SearchCommand extends Command
{
    protected static $defaultName = 'fos:elastica:search';

    private $indexManager;

    public function __construct(IndexManager $indexManager)
    {
        parent::__construct();

        $this->indexManager = $indexManager;
    }

    protected function configure()
    {
        $this
            ->setName('fos:elastica:search')
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

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $indexName = $input->getOption('index');
        $index = $this->indexManager->getIndex($indexName ? $indexName : null);
        $query = Query::create($input->getArgument('query'));
        $query->setSize($input->getOption('limit'));
        if ($input->getOption('explain')) {
            $query->setExplain(true);
        }

        $resultSet = $index->search($query);

        $output->writeLn(sprintf('Found %d results', $index->count($query)));
        foreach ($resultSet->getResults() as $result) {
            $output->writeLn($this->formatResult($result, $input->getOption('show-field'), $input->getOption('show-source'), $input->getOption('show-id'), $input->getOption('explain')));
        }

        return 0;
    }

    /**
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
            $toString = $source[$showField] ?? '-';
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
