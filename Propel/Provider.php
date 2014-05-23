<?php

namespace FOS\ElasticaBundle\Propel;

use FOS\ElasticaBundle\Provider\AbstractProvider;
use Elastica\Exception\Bulk\ResponseException as BulkResponseException;

/**
 * Propel provider
 *
 * @author William Durand <william.durand1@gmail.com>
 */
class Provider extends AbstractProvider
{
    /**
     * {@inheritdoc}
     */
    public function populate(\Closure $loggerClosure = null, array $options = array())
    {
        $queryClass = $this->objectClass . 'Query';
        $nbObjects = $queryClass::create()->count();
        $offset = isset($options['offset']) ? intval($options['offset']) : 0;
        $sleep = isset($options['sleep']) ? intval($options['sleep']) : 0;
        $batchSize = isset($options['batch-size']) ? intval($options['batch-size']) : $this->options['batch_size'];
        $ignoreErrors = isset($options['ignore-errors']) ? $options['ignore-errors'] : $this->options['ignore_errors'];

        if ($loggerClosure && !$this->options['disable_logging']) {
            $loggerClosure = null;
        }

        for (; $offset < $nbObjects; $offset += $batchSize) {
            if ($loggerClosure) {
                $stepStartTime = microtime(true);
            }

            $objects = $queryClass::create()
                ->limit($batchSize)
                ->offset($offset)
                ->find();

            try {
                $this->objectPersister->insertMany($objects->getArrayCopy());
            } catch(BulkResponseException $e) {
                if ($ignoreErrors && $loggerClosure) {
                    $loggerClosure(sprintf('<error>%s</error>',$e->getMessage()));
                } else {
                    throw $e;
                }
            }

            usleep($sleep);

            if ($loggerClosure) {
                $stepNbObjects = count($objects);
                $stepCount = $stepNbObjects + $offset;
                $percentComplete = 100 * $stepCount / $nbObjects;
                $objectsPerSecond = $stepNbObjects / (microtime(true) - $stepStartTime);
                $loggerClosure(sprintf('%0.1f%% (%d/%d), %d objects/s %s', $percentComplete, $stepCount, $nbObjects, $objectsPerSecond, $this->getMemoryUsage()));
            }
        }
    }
}
