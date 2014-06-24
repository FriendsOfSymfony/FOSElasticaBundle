<?php

namespace FOS\ElasticaBundle\Doctrine;

use Doctrine\Common\Persistence\ManagerRegistry;
use Elastica\Exception\Bulk\ResponseException as BulkResponseException;
use FOS\ElasticaBundle\Persister\ObjectPersisterInterface;
use FOS\ElasticaBundle\Provider\AbstractProvider as BaseAbstractProvider;
use FOS\ElasticaBundle\Provider\IndexableInterface;

abstract class AbstractProvider extends BaseAbstractProvider
{
    protected $managerRegistry;

    /**
     * Constructor.
     *
     * @param ObjectPersisterInterface $objectPersister
     * @param IndexableInterface $indexable
     * @param string $objectClass
     * @param array $options
     * @param ManagerRegistry $managerRegistry
     */
    public function __construct(
        ObjectPersisterInterface $objectPersister,
        IndexableInterface $indexable,
        $objectClass,
        array $options,
        ManagerRegistry $managerRegistry
    ) {
        parent::__construct($objectPersister, $indexable, $objectClass, array_merge(array(
            'clear_object_manager' => true,
            'debug_logging'        => false,
            'ignore_errors'        => false,
            'query_builder_method' => 'createQueryBuilder',
        ), $options));

        $this->managerRegistry = $managerRegistry;
    }

    /**
     * @see FOS\ElasticaBundle\Provider\ProviderInterface::populate()
     */
    public function populate(\Closure $loggerClosure = null, array $options = array())
    {
        if (!$this->options['debug_logging']) {
            $logger = $this->disableLogging();
        }

        $queryBuilder = $this->createQueryBuilder();
        $nbObjects = $this->countObjects($queryBuilder);
        $offset = isset($options['offset']) ? intval($options['offset']) : 0;
        $sleep = isset($options['sleep']) ? intval($options['sleep']) : 0;
        $batchSize = isset($options['batch-size']) ? intval($options['batch-size']) : $this->options['batch_size'];
        $ignoreErrors = isset($options['ignore-errors']) ? $options['ignore-errors'] : $this->options['ignore_errors'];
        $manager = $this->managerRegistry->getManagerForClass($this->objectClass);

        for (; $offset < $nbObjects; $offset += $batchSize) {
            if ($loggerClosure) {
                $stepStartTime = microtime(true);
            }
            $objects = $this->fetchSlice($queryBuilder, $batchSize, $offset);
            if ($loggerClosure) {
                $stepNbObjects = count($objects);
            }
            $objects = array_filter($objects, array($this, 'isObjectIndexable'));
            if (!$objects) {
                if ($loggerClosure) {
                    $loggerClosure('<info>Entire batch was filtered away, skipping...</info>');
                }

                continue;
            }

            if (!$ignoreErrors) {
                $this->objectPersister->insertMany($objects);
            } else {
                try {
                    $this->objectPersister->insertMany($objects);
                } catch(BulkResponseException $e) {
                    if ($loggerClosure) {
                        $loggerClosure(sprintf('<error>%s</error>',$e->getMessage()));
                    }
                }
            }

            if ($this->options['clear_object_manager']) {
                $manager->clear();
            }

            usleep($sleep);

            if ($loggerClosure) {
                $stepCount = $stepNbObjects + $offset;
                $percentComplete = 100 * $stepCount / $nbObjects;
                $timeDifference = microtime(true) - $stepStartTime;
                $objectsPerSecond = $timeDifference ? ($stepNbObjects / $timeDifference) : $stepNbObjects;
                $loggerClosure(sprintf('%0.1f%% (%d/%d), %d objects/s %s', $percentComplete, $stepCount, $nbObjects, $objectsPerSecond, $this->getMemoryUsage()));
            }
        }

        if (!$this->options['debug_logging']) {
            $this->enableLogging($logger);
        }
    }

    /**
     * Counts objects that would be indexed using the query builder.
     *
     * @param object $queryBuilder
     * @return integer
     */
    protected abstract function countObjects($queryBuilder);

    /**
     * Disables logging and returns the logger that was previously set.
     *
     * @return mixed
     */
    protected abstract function disableLogging();

    /**
     * Reenables the logger with the previously returned logger from disableLogging();
     *
     * @param mixed $logger
     * @return mixed
     */
    protected abstract function enableLogging($logger);

    /**
     * Fetches a slice of objects using the query builder.
     *
     * @param object  $queryBuilder
     * @param integer $limit
     * @param integer $offset
     * @return array
     */
    protected abstract function fetchSlice($queryBuilder, $limit, $offset);

    /**
     * Creates the query builder, which will be used to fetch objects to index.
     *
     * @return object
     */
    protected abstract function createQueryBuilder();
}
