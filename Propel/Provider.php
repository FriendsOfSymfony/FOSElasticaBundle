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
    public function populate(\Closure $loggerClosure = null, array $options = array())
    {
        $queryClass = $this->objectClass . 'Query';
        $nbObjects = $queryClass::create()->count();
        $offset = isset($options['offset']) ? intval($options['offset']) : 0;
        $sleep = isset($options['sleep']) ? intval($options['sleep']) : 0;
        $progressBar = isset($options['progress-bar']) ? boolval($options['progress-bar']) : false;
        $batchSize = isset($options['batch-size']) ? intval($options['batch-size']) : $this->options['batch_size'];

        for (; $offset < $nbObjects; $offset += $batchSize) {
            if ($loggerClosure) {
                $stepStartTime = microtime(true);
            }

            $objects = $queryClass::create()
                ->limit($batchSize)
                ->offset($offset)
                ->find()
                ->getArrayCopy();
            if ($loggerClosure) {
                $stepNbObjects = count($objects);
            }
            $objects = array_filter($objects, array($this, 'isObjectIndexable'));
            if (!$objects) {
                $loggerClosure('<info>Entire batch was filtered away, skipping...</info>');

                continue;
            }

            $this->objectPersister->insertMany($objects);

            usleep($sleep);

            if ($loggerClosure && !$progressBar) {
                $stepCount = $stepNbObjects + $offset;
                $percentComplete = 100 * $stepCount / $nbObjects;
                $objectsPerSecond = $stepNbObjects / (microtime(true) - $stepStartTime);
                $loggerClosure(sprintf('%0.1f%% (%d/%d), %d objects/s %s', $percentComplete, $stepCount, $nbObjects, $objectsPerSecond, $this->getMemoryUsage()));
            } else if ($loggerClosure && $progressBar) {
                $loggerClosure($stepNbObjects);
            }
        }
    }
}
