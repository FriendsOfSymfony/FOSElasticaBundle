<?php

namespace FOQ\ElasticaBundle\Doctrine;

use FOQ\ElasticaBundle\Provider\ProviderInterface;
use FOQ\ElasticaBundle\Persister\ObjectPersisterInterface;
use Elastica_Type;
use Elastica_Document;
use Closure;
use InvalidArgumentException;

abstract class AbstractProvider implements ProviderInterface
{
    /**
     * Elastica type
     *
     * @var Elastica_Type
     */
    protected $type;

    /**
     * Domain model object manager
     *
     * @var object
     */
    protected $objectManager;

    /**
     * Object persister
     *
     * @var ObjectPersisterInterface
     */
    protected $objectPersister;

    /**
     * Provider options
     *
     * @var array
     */
    protected $options = array(
        'batch_size'           => 100,
        'clear_object_manager' => true,
		'query_builder_method' => 'createQueryBuilder'
    );

    public function __construct(Elastica_Type $type, $objectManager, ObjectPersisterInterface $objectPersister, $objectClass, array $options = array())
    {
        $this->type            = $type;
        $this->objectManager   = $objectManager;
        $this->objectClass     = $objectClass;
        $this->objectPersister = $objectPersister;
        $this->options         = array_merge($this->options, $options);
    }

    /**
     * Insert the repository objects in the type index
     *
     * @param Closure $loggerClosure
     */
    public function populate(Closure $loggerClosure)
    {
        $queryBuilder = $this->createQueryBuilder();
        $nbObjects    = $this->countObjects($queryBuilder);

        for ($offset = 0; $offset < $nbObjects; $offset += $this->options['batch_size']) {

            $stepStartTime = microtime(true);
            $documents = array();
            $objects = $this->fetchSlice($queryBuilder, $this->options['batch_size'], $offset);

            $this->objectPersister->insertMany($objects);

            if ($this->options['clear_object_manager']) {
                $this->objectManager->clear();
            }

            $stepNbObjects = count($objects);
            $stepCount = $stepNbObjects+$offset;
            $objectsPerSecond = $stepNbObjects / (microtime(true) - $stepStartTime);
            $loggerClosure(sprintf('%0.1f%% (%d/%d), %d objects/s', 100*$stepCount/$nbObjects, $stepCount, $nbObjects, $objectsPerSecond));
        }
    }

    /**
     * Counts the objects of a query builder
     *
     * @param queryBuilder
     * @return int
     **/
    protected abstract function countObjects($queryBuilder);

    /**
     * Fetches a slice of objects
     *
     * @param queryBuilder
     * @param int limit
     * @param int offset
     * @return array of objects
     **/
    protected abstract function fetchSlice($queryBuilder, $limit, $offset);

    /**
     * Creates the query builder used to fetch the documents to index
     *
     * @return query builder
     **/
    protected abstract function createQueryBuilder();
}
