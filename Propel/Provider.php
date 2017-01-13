<?php

namespace FOS\ElasticaBundle\Propel;

use FOS\ElasticaBundle\Provider\AbstractProvider;

/**
 * Propel provider.
 *
 * @author William Durand <william.durand1@gmail.com>
 */
class Provider extends AbstractProvider
{
    /**
     * {@inheritDoc}
     */
    public function doPopulate($options, \Closure $loggerClosure = null)
    {
        $queryClass = $this->objectClass.'Query';
        $nbObjects = $queryClass::create()->count();

        $offset = $options['offset'];

        for (; $offset < $nbObjects; $offset += $options['batch_size']) {
            $objects = $queryClass::create()
                ->limit($options['batch_size'])
                ->offset($offset)
                ->find()
                ->getArrayCopy();
            $objects = $this->filterObjects($options, $objects);
            if (!empty($objects)) {
                $this->objectPersister->insertMany($objects);
            }

            usleep($options['sleep']);

            if ($loggerClosure) {
                $loggerClosure($options['batch_size'], $nbObjects);
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function configureOptions()
    {
        parent::configureOptions();

        $this->resolver->setDefaults(array(
            'clear_object_manager' => true,
            'debug_logging'        => false,
            'ignore_errors'        => false,
            'offset'               => 0,
            'query_builder_method' => 'createQueryBuilder',
            'sleep'                => 0
        ));
    }

    /**
     * {@inheritDoc}
     */
    protected function disableLogging()
    {
    }

    /**
     * {@inheritDoc}
     */
    protected function enableLogging($logger)
    {
    }
}
