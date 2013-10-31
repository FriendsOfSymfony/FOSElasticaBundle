<?php

namespace FOS\ElasticaBundle\Propel;

use FOS\ElasticaBundle\Provider\AbstractProvider;

/**
 * Propel provider
 *
 * @author William Durand <william.durand1@gmail.com>
 */
class Provider extends AbstractProvider
{
    /**
     * @see FOS\ElasticaBundle\Provider\ProviderInterface::populate()
     */
    public function populate(\Closure $loggerClosure = null)
    {
        $queryClass = $this->objectClass . 'Query';
        $nbObjects = $queryClass::create()->count();

        for ($offset = 0; $offset < $nbObjects; $offset += $this->options['batch_size']) {
            if ($loggerClosure) {
                $stepStartTime = microtime(true);
            }

            $objects = $queryClass::create()
                ->limit($this->options['batch_size'])
                ->offset($offset)
                ->find();

            $this->objectPersister->insertMany($objects->getArrayCopy());

            if ($loggerClosure) {
                $stepNbObjects = count($objects);
                $stepCount = $stepNbObjects + $offset;
                $percentComplete = 100 * $stepCount / $nbObjects;
                $objectsPerSecond = $stepNbObjects / (microtime(true) - $stepStartTime);
                $loggerClosure(sprintf('%0.1f%% (%d/%d), %d objects/s', $percentComplete, $stepCount, $nbObjects, $objectsPerSecond));
            }
        }
    }
}
