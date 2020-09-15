<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Command;

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
    protected static $defaultName = 'fos:elastica:delete';

    private $client;
    private $indexManager;

    public function __construct(
        Client $client,
        IndexManager $indexManager
    ) {
        parent::__construct();

        $this->client = $client;
        $this->indexManager = $indexManager;
    }

    protected function configure()
    {
        $this
            ->addOption('index', null, InputOption::VALUE_OPTIONAL, 'Index that needs to be deleted')
            ->setDescription('Deleting an index')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $indexName = $input->getOption('index');
        $indexes = null === $indexName ? array_keys($this->indexManager->getAllIndexes()) : [$indexName];

        foreach ($indexes as $indexName) {
            $output->writeln(
                sprintf('<info>Deleting</info> <comment>%s</comment> ', $indexName)
            );
            $index = $this->indexManager->getIndex($indexName);
            if (!$index->exists()) {
                $output->writeln(
                    sprintf('<error>%s does not exist and can\'t be deleted</error>', $indexName)
                );

                continue;
            }

            $this->deleteIndex($index->getName());
        }

        return 0;
    }

    private function deleteIndex(string $indexName): void
    {
        try {
            $path = $indexName;
            $this->client->request($path, Request::DELETE);
        } catch (ExceptionInterface $deleteOldIndexException) {
            throw new \RuntimeException(sprintf('Failed to delete index "%s" with message: "%s"', $indexName, $deleteOldIndexException->getMessage()), 0, $deleteOldIndexException);
        }
    }
}
