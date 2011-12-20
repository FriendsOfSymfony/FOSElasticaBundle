<?php

namespace FOQ\ElasticaBundle\Propel;

use FOQ\ElasticaBundle\Provider\ProviderInterface;
use FOQ\ElasticaBundle\Persister\ObjectPersisterInterface;
use Elastica_Type;
use Elastica_Document;
use Closure;
use InvalidArgumentException;

/**
 * Propel provider
 *
 * @author William Durand <william.durand1@gmail.com>
 */
class Provider implements ProviderInterface
{
    /**
     * Elastica type
     *
     * @var Elastica_Type
     */
    protected $type;

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
    );

    public function __construct(Elastica_Type $type, ObjectPersisterInterface $objectPersister, $objectClass, array $options = array())
    {
        $this->type            = $type;
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
        $queryClass = $this->objectClass . 'Query';
        $nbObjects  = $queryClass::create()->count();

        for ($offset = 0; $offset < $nbObjects; $offset += $this->options['batch_size']) {

            $stepStartTime = microtime(true);
            $objects = $queryClass::create()
                ->limit($this->options['batch_size'])
                ->offset($offset)
		->find();

            $this->objectPersister->insertMany($objects->getArrayCopy());

            $stepNbObjects = count($objects);
            $stepCount = $stepNbObjects+$offset;
            $objectsPerSecond = $stepNbObjects / (microtime(true) - $stepStartTime);
            $loggerClosure(sprintf('%0.1f%% (%d/%d), %d objects/s', 100*$stepCount/$nbObjects, $stepCount, $nbObjects, $objectsPerSecond));
        }
    }
}
