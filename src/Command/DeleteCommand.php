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

use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\ElasticsearchException;
use Elastic\Elasticsearch\Exception\MissingParameterException;
use Elastic\Elasticsearch\Exception\ServerResponseException;
use Elastica\Exception\ExceptionInterface;
use Elastica\Request;
use FOS\ElasticaBundle\Elastica\Client;
use FOS\ElasticaBundle\Index\IndexManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Jan Schädlich <jan.schaedlich@sensiolabs.de>
 */
class DeleteCommand extends Command
{
    private IndexManager $indexManager;

    public function __construct(
        IndexManager $indexManager
    ) {
        parent::__construct();

        $this->indexManager = $indexManager;
    }

    /**
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('fos:elastica:delete')
            ->addOption('index', null, InputOption::VALUE_OPTIONAL, 'Index that needs to be deleted')
            ->setDescription('Deleting an index')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $indexName = $input->getOption('index');
        $indexes = null === $indexName ? \array_keys($this->indexManager->getAllIndexes()) : [$indexName];

        foreach ($indexes as $indexName) {
            $output->writeln(
                \sprintf('<info>Deleting</info> <comment>%s</comment> ', $indexName)
            );
            $index = $this->indexManager->getIndex($indexName);
            if (!$index->exists()) {
                $output->writeln(
                    \sprintf('<error>%s does not exist and can\'t be deleted</error>', $indexName)
                );

                continue;
            }

            try {
                $index->delete();
            } catch (ElasticsearchException $deleteOldIndexException) {
                throw new \RuntimeException(\sprintf('Failed to delete index "%s" with message: "%s"', $indexName, $deleteOldIndexException->getMessage()), 0, $deleteOldIndexException);
            }
        }

        return 0;
    }
}
