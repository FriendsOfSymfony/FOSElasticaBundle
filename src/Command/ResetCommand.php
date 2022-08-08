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

use FOS\ElasticaBundle\Index\IndexManager;
use FOS\ElasticaBundle\Index\Resetter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Reset search indexes.
 */
class ResetCommand extends Command
{
    private $indexManager;
    private $resetter;

    public function __construct(
        IndexManager $indexManager,
        Resetter $resetter
    ) {
        parent::__construct();

        $this->indexManager = $indexManager;
        $this->resetter = $resetter;
    }

    /**
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('fos:elastica:reset')
            ->addOption('index', null, InputOption::VALUE_OPTIONAL, 'The index to reset')
            ->addOption('force', null, InputOption::VALUE_NONE, 'Force index deletion if same name as alias')
            ->setDescription('Reset search indexes')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $indexes = (null !== $index = $input->getOption('index')) ? [$index] : \array_keys($this->indexManager->getAllIndexes());
        $force = (bool) $input->getOption('force');

        foreach ($indexes as $index) {
            $output->writeln(\sprintf('<info>Resetting</info> <comment>%s</comment>', $index));
            $this->resetter->resetIndex($index, false, $force);
        }

        return 0;
    }
}
