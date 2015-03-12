<?php

namespace FOS\ElasticaBundle\Doctrine;

use Doctrine\Common\Persistence\ManagerRegistry;
use Elastica\Exception\Bulk\ResponseException as BulkResponseException;
use FOS\ElasticaBundle\Persister\ObjectPersisterInterface;
use FOS\ElasticaBundle\Provider\AbstractProvider as BaseAbstractProvider;
use FOS\ElasticaBundle\Provider\IndexableInterface;

abstract class AbstractProvider extends BaseAbstractProvider
{
    /**
     * @var SliceFetcherInterface
     */
    private $sliceFetcher;

    /**
     * @var ManagerRegistry
     */
    protected $managerRegistry;

    /**
     * Constructor.
     *
     * @param ObjectPersisterInterface $objectPersister
     * @param IndexableInterface       $indexable
     * @param string                   $objectClass
     * @param array                    $options
     * @param ManagerRegistry          $managerRegistry
     * @param SliceFetcherInterface    $sliceFetcher
     */
    public function __construct(
        ObjectPersisterInterface $objectPersister,
        IndexableInterface $indexable,
        $objectClass,
        array $options,
        ManagerRegistry $managerRegistry,
        SliceFetcherInterface $sliceFetcher = null
    ) {
        parent::__construct($objectPersister, $indexable, $objectClass, array_merge(array(
            'clear_object_manager' => true,
            'debug_logging'        => false,
            'ignore_errors'        => false,
            'query_builder_method' => 'createQueryBuilder',
        ), $options));

        $this->managerRegistry = $managerRegistry;
        $this->sliceFetcher = $sliceFetcher;
    }

    /**
     * {@inheritDoc}
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

        $objects = array();
        for (; $offset < $nbObjects; $offset += $batchSize) {
            $objects = $this->getSlice($queryBuilder, $batchSize, $offset, $objects);
            $objects = array_filter($objects, array($this, 'isObjectIndexable'));

            if (!empty($objects)) {
                if (!$ignoreErrors) {
                    $this->objectPersister->insertMany($objects);
                } else {
                    try {
                        $this->objectPersister->insertMany($objects);
                    } catch (BulkResponseException $e) {
                        if ($loggerClosure) {
                            $loggerClosure($batchSize, $nbObjects, sprintf('<error>%s</error>', $e->getMessage()));
                        }
                    }
                }
            }

            if ($this->options['clear_object_manager']) {
                $manager->clear();
            }

            usleep($sleep);

            if ($loggerClosure) {
                $loggerClosure($batchSize, $nbObjects);
            }
        }

        if (!$this->options['debug_logging']) {
            $this->enableLogging($logger);
        }
    }

    /**
     * If this Provider has a SliceFetcher defined, we use it instead of falling back to
     * the fetchSlice methods defined in the ORM/MongoDB subclasses.
     *
     * @param $queryBuilder
     * @param int   $limit
     * @param int   $offset
     * @param array $lastSlice
     *
     * @return array
     */
    protected function getSlice($queryBuilder, $limit, $offset, $lastSlice)
    {
        if (!$this->sliceFetcher) {
            return $this->fetchSlice($queryBuilder, $limit, $offset);
        }

        $manager = $this->managerRegistry->getManagerForClass($this->objectClass);
        $identifierFieldNames = $manager
            ->getClassMetadata($this->objectClass)
            ->getIdentifierFieldNames();

        return $this->sliceFetcher->fetch(
            $queryBuilder,
            $limit,
            $offset,
            $lastSlice,
            $identifierFieldNames
        );
    }

    /**
     * Counts objects that would be indexed using the query builder.
     *
     * @param object $queryBuilder
     *
     * @return integer
     */
    abstract protected function countObjects($queryBuilder);

    /**
     * Disables logging and returns the logger that was previously set.
     *
     * @return mixed
     */
    abstract protected function disableLogging();

    /**
     * Reenables the logger with the previously returned logger from disableLogging();.
     *
     * @param mixed $logger
     *
     * @return mixed
     */
    abstract protected function enableLogging($logger);

    /**
     * Fetches a slice of objects using the query builder.
     *
     * @param object  $queryBuilder
     * @param integer $limit
     * @param integer $offset
     *
     * @return array
     */
    abstract protected function fetchSlice($queryBuilder, $limit, $offset);

    /**
     * Creates the query builder, which will be used to fetch objects to index.
     *
     * @return object
     */
    abstract protected function createQueryBuilder();
}
