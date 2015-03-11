<?php
/**
 * Created by PhpStorm.
 * User: dominikkasprzak
 * Date: 27/02/15
 * Time: 08:49
 */

namespace FOS\ElasticaBundle\Command;


use Elastica\Bulk;
use FOS\ElasticaBundle\Configuration\ConfigManager;
use FOS\ElasticaBundle\Elastica\Index;
use FOS\ElasticaBundle\Index\AliasProcessor;
use FOS\ElasticaBundle\Index\IndexManager;
use FOS\ElasticaBundle\Index\Reindexer;
use FOS\ElasticaBundle\Index\Resetter;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ReindexCommand extends ContainerAwareCommand
{
    /**
     * @var AliasProcessor
     */
    private $aliasProcessor;

    /**
     * @var ConfigManager
     */
    private $configManager;

    /**
     * @var IndexManager
     */
    private $indexManager;

    /**
     * @var Resetter
     */
    private $resetter;

    /**
     * @var Reindexer
     */
    private $reindexer;

    /**
     * @var LoggerClosureHelper
     */
    private $loggerClosureHelper;

    /**
     * @see Symfony\Component\Console\Command\Command::configure()
     */
    protected function configure()
    {
        $this
            ->setName('fos:elastica:reindex')
            ->addOption('index', null, InputOption::VALUE_OPTIONAL, 'The index to reindex')
            ->addOption('batch-size', null, InputOption::VALUE_REQUIRED, 'Index packet size')
            ->addOption('ignore-errors', null, InputOption::VALUE_NONE, 'Do not stop on errors')
            ->setDescription('Reindex one or all ElasticSearch indices')
        ;
    }

    /**
     * @see Symfony\Component\Console\Command\Command::initialize()
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->aliasProcessor = $this->getContainer()->get('fos_elastica.alias_processor');
        $this->configManager = $this->getContainer()->get('fos_elastica.config_manager');
        $this->indexManager = $this->getContainer()->get('fos_elastica.index_manager');
        $this->resetter = $this->getContainer()->get('fos_elastica.resetter');
        $this->reindexer = $this->getContainer()->get('fos_elastica.reindexer');
        $this->loggerClosureHelper = new LoggerClosureHelper();
    }

    /**
     * @see Symfony\Component\Console\Command\Command::execute()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $index         = $input->getOption('index');
        $options       = $input->getOptions();
        $options['ignore-errors'] = $input->hasOption('ignore-errors');

        if (null !== $index) {
            $this->reindex($output, $index, $options);
        } else {
            $indexes = array_keys($this->indexManager->getAllIndexes());

            foreach ($indexes as $index) {
                $this->reindex($output, $index, $options);
            }
        }
    }

    private function reindex(OutputInterface $output, $indexName, $options)
    {
        if (! $this->configManager->hasIndexConfiguration($indexName)) {
            throw new \InvalidArgumentException();
        }

        $indexConfig = $this->configManager->getIndexConfiguration($indexName);
        if (! $indexConfig->isUseAlias()) {
            $output->writeln(sprintf("<error>Index %s not using alias, cannot reindex</error>", $indexName));
            return;
        }

        $newIndex = $this->indexManager->getIndex($indexName);
        $client = $newIndex->getClient();

        if (! $newIndex) {
            $output->writeln(sprintf("<error>Index %s not found</error>", $indexName));
            return;
        }

        $aliasedIndices = $this->aliasProcessor->getAliasedIndexes($client, $indexConfig->getElasticSearchName());
        if (1 !== count($aliasedIndices)) {
            $output->writeln(sprintf("<error>Alias %s points to an incorrect number of indices:</error> <comment>%d</comment>", $indexName, count($aliasedIndices)));
            return;
        }
        $oldIndex = new Index($client, reset($aliasedIndices));

        $output->writeln(sprintf('<info>Reindexing</info> <comment>%s</comment>', $indexName));

        $this->resetter->resetIndex($indexName, /* $populating */ true);
        foreach ($indexConfig->getTypes() as $typeConfig) {
            $this->resetter->resetIndexType($indexName, $typeConfig->getName());
        }

        $startTime = $this->militime();

        $this->reindexer->copyDocuments(
            $oldIndex,
            $newIndex,
            $this->loggerClosureHelper->getLoggerClosure($output, '<info>Reindexing</info> <comment>%s</comment>', array($indexName)),
            $options
        );

        $output->writeln(sprintf('<info>Switching alias</info>'));
        $this->resetter->postPopulate($indexName, /* $deleteOldIndex */ false);

        $output->writeln(sprintf('<info>Updating changes made during reindex</info>'));

        $postPopulateErrors = $this->reindexer->copyDocuments(
            $oldIndex,
            $newIndex,
            $this->loggerClosureHelper->getLoggerClosure($output, '<info>Updating changes</info>'),
            array_merge($options, array('ignore-errors' => true)),  //  Errors are possible due to version colisions.
            array('range' => array('_timestamp' => array('gt' => $startTime)))
        );

        if ($postPopulateErrors) {
            $output->writeln(sprintf('<info>Completed with</info> <comment>%s</comment> <info>errors</info>', $postPopulateErrors));
        } else {
            $output->writeln(sprintf('<info>Completed without errors</info>'));
        }

        $output->writeln(sprintf('<info>Removing old index</info> <comment>%s</comment>', $oldIndex->getName()));
        $oldIndex->delete();
    }

    private function militime() {
        $microtime = microtime();
        $comps = explode(' ', $microtime);

        // Note: Using a string here to prevent loss of precision
        // in case of "overflow" (PHP converts it to a double)
        return sprintf('%d%03d', $comps[1], $comps[0] * 1000);
    }
}
