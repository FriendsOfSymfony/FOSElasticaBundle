<?php

namespace FOS\ElasticaBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use FOS\ElasticaBundle\IndexManager;
use FOS\ElasticaBundle\Resetter;

/**
 * Reset search indexes.
 */
class ResetCommand extends ContainerAwareCommand
{
    /**
     * @var IndexManager
     */
    private $indexManager;

    /**
     * @var Resetter
     */
    private $resetter;

    /**
     * @see Symfony\Component\Console\Command\Command::configure()
     */
    protected function configure()
    {
        $this
            ->setName('fos:elastica:reset')
            ->addOption('index', null, InputOption::VALUE_OPTIONAL, 'The index to reset')
            ->addOption('index-template', null, InputOption::VALUE_OPTIONAL, 'The index template to reset')
            ->addOption('type', null, InputOption::VALUE_OPTIONAL, 'The type to reset')
            ->addOption('force', null, InputOption::VALUE_NONE, 'Force index deletion if same name as alias or index matches index template pattern')
            ->setDescription('Reset search indexes')
        ;
    }

    /**
     * @see Symfony\Component\Console\Command\Command::initialize()
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->indexManager = $this->getContainer()->get('fos_elastica.index_manager');
        $this->resetter = $this->getContainer()->get('fos_elastica.resetter');
    }

    /**
     * @see Symfony\Component\Console\Command\Command::execute()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $index = $input->getOption('index');
        $indexTemplate = $input->getOption('index-template');
        $type = $input->getOption('type');
        $force = (bool) $input->getOption('force');

        if (null === $index && null !== $type) {
            throw new \InvalidArgumentException('Cannot specify type option without an index.');
        }

        if ($indexTemplate && $index) {
            throw new \InvalidArgumentException('Only index or index template name can by specify at the same time.');
        }

        if ($indexTemplate) {
            $output->writeln(sprintf('<info>Resetting template</info> <comment>%s</comment>', $indexTemplate));
            $this->resetter->resetTemplate($indexTemplate, $force);
        } elseif (null !== $type) {
            $output->writeln(sprintf('<info>Resetting templates</info>'));
            $output->writeln(sprintf('<info>Resetting</info> <comment>%s/%s</comment>', $index, $type));
            $this->resetter->resetAllTemplates();
            $this->resetter->resetIndexType($index, $type);
        } else {
            $indexes = null === $index
                ? array_keys($this->indexManager->getAllIndexes())
                : array($index)
            ;

            $output->writeln(sprintf('<info>Resetting templates</info>'));
            $this->resetter->resetAllTemplates();

            foreach ($indexes as $index) {
                $output->writeln(sprintf('<info>Resetting</info> <comment>%s</comment>', $index));
                $this->resetter->resetIndex($index, false, $force);
            }
        }
    }
}
