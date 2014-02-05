<?php

namespace FOS\ElasticaBundle\Doctrine;

use Doctrine\Common\Persistence\ManagerRegistry;
use Elastica\Exception\Bulk\ResponseException as BulkResponseException;
use FOS\ElasticaBundle\Persister\ObjectPersisterInterface;
use FOS\ElasticaBundle\Provider\AbstractProvider as BaseAbstractProvider;

abstract class AbstractProvider extends BaseAbstractProvider
{
    protected $managerRegistry;

    /**
     * Constructor.
     *
     * @param ObjectPersisterInterface $objectPersister
     * @param string                   $objectClass
     * @param array                    $options
     * @param ManagerRegistry          $managerRegistry
     */
    public function __construct(ObjectPersisterInterface $objectPersister, $objectClass, array $options, $managerRegistry)
    {
        parent::__construct($objectPersister, $objectClass, array_merge(array(
            'clear_object_manager' => true,
            'query_builder_method' => 'createQueryBuilder',
            'stop_on_error'        => true,
        ), $options));

        $this->managerRegistry = $managerRegistry;
    }

    /**
     * @see FOS\ElasticaBundle\Provider\ProviderInterface::populate()
     */
    public function populate(\Closure $loggerClosure = null, array $options = array())
    {
        $queryBuilder = $this->createQueryBuilder();
        $nbObjects = $this->countObjects($queryBuilder);
        $offset = isset($options['offset']) ? intval($options['offset']) : 0;
        $sleep = isset($options['sleep']) ? intval($options['sleep']) : 0;
        $batchSize = isset($options['batch-size']) ? intval($options['batch-size']) : $this->options['batch_size'];
        $stopOnError = isset($options['no-stop-on-error']) ? empty($options['no-stop-on-error']) : $this->options['stop_on_error'];

        for (; $offset < $nbObjects; $offset += $batchSize) {
            if ($loggerClosure) {
                $stepStartTime = microtime(true);
            }
            $objects = $this->fetchSlice($queryBuilder, $batchSize, $offset);

            if (!$stopOnError) {
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
                $this->managerRegistry->getManagerForClass($this->objectClass)->clear();
            }

            usleep($sleep);

            if ($loggerClosure) {
                $stepNbObjects = count($objects);
                $stepCount = $stepNbObjects + $offset;
                $percentComplete = 100 * $stepCount / $nbObjects;
                $objectsPerSecond = $stepNbObjects / (microtime(true) - $stepStartTime);
                $loggerClosure(sprintf('%0.1f%% (%d/%d), %d objects/s', $percentComplete, $stepCount, $nbObjects, $objectsPerSecond));
            }
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
