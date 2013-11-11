<?php

namespace FOS\ElasticaBundle\Doctrine;

use Doctrine\Common\Persistence\ManagerRegistry;
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
        ), $options));

        $this->managerRegistry = $managerRegistry;
    }

    /**
     * @see FOS\ElasticaBundle\Provider\ProviderInterface::populate()
     */
    public function populate(\Closure $loggerClosure = null)
    {
        $queryBuilder = $this->createQueryBuilder();
        $nbObjects = $this->countObjects($queryBuilder);

        for ($offset = 0; $offset < $nbObjects; $offset += $this->options['batch_size']) {
            if ($loggerClosure) {
                $stepStartTime = microtime(true);
            }
            $objects = $this->fetchSlice($queryBuilder, $this->options['batch_size'], $offset);

            $this->objectPersister->insertMany($objects);

            if ($this->options['clear_object_manager']) {
                $this->managerRegistry->getManagerForClass($this->objectClass)->clear();
            }

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
