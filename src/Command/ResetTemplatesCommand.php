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

use FOS\ElasticaBundle\Index\TemplateResetter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * Reset search indexes templates.
 */
final class ResetTemplatesCommand extends Command
{
    protected static $defaultName = 'fos:elastica:reset-templates';

    /** @var TemplateResetter */
    private $resetter;

    public function __construct(
        TemplateResetter $resetter
    ) {
        parent::__construct();

        $this->resetter = $resetter;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('fos:elastica:reset-templates')
            ->addOption(
                'index',
                null,
                InputOption::VALUE_REQUIRED,
                'The index template to reset. If no index template name specified than all templates will be reset'
            )
            ->addOption(
                'force-delete',
                null,
                InputOption::VALUE_NONE,
                'Delete all indexes that matches index templates patterns. '.
                'Aware that pattern may match various indexes.'
            )
            ->setDescription('Reset search indexes templates')
        ;
    }

    /**
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $indexTemplate = $input->getOption('index');
        $deleteByPattern = $input->getOption('force-delete');

        if ($input->isInteractive() && $deleteByPattern) {
            $helper = $this->getHelper('question');
            $question = new ConfirmationQuestion('You are going to remove all template indexes. Are you sure?', false);

            if (!$helper->ask($input, $output, $question)) {
                return 1;
            }
        }

        if (null !== $indexTemplate) {
            $output->writeln(\sprintf('<info>Resetting template</info> <comment>%s</comment>', $indexTemplate));
            $this->resetter->resetIndex($indexTemplate, $deleteByPattern);
        } else {
            $output->writeln('<info>Resetting all templates</info>');
            $this->resetter->resetAllIndexes($deleteByPattern);
        }

        return 0;
    }
}
