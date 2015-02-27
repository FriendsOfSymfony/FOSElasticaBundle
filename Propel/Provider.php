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
     * {@inheritDoc}
     */
    public function populate(\Closure $loggerClosure = null, array $options = array())
    {
        $queryClass = $this->objectClass . 'Query';
        $nbObjects = $queryClass::create()->count();
        $offset = isset($options['offset']) ? intval($options['offset']) : 0;
        $sleep = isset($options['sleep']) ? intval($options['sleep']) : 0;
        $batchSize = isset($options['batch-size']) ? intval($options['batch-size']) : $this->options['batch_size'];

        for (; $offset < $nbObjects; $offset += $batchSize) {
            $objects = $queryClass::create()
                ->limit($batchSize)
                ->offset($offset)
                ->find()
                ->getArrayCopy();

            $objects = array_filter($objects, array($this, 'isObjectIndexable'));
            if ($objects) {
                $this->objectPersister->insertMany($objects);
            }

            usleep($sleep);

            if ($loggerClosure) {
                $loggerClosure(count($objects), $nbObjects);
            }
        }
    }
}
