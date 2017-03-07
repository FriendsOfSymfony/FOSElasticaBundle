<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
     * {@inheritdoc}
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
            $sliceSize = count($objects);
            $objects = $this->filterObjects($options, $objects);
            if (!empty($objects)) {
                $this->objectPersister->insertMany($objects);
            }

            usleep($options['sleep']);

            if ($loggerClosure) {
                $loggerClosure($sliceSize, $nbObjects);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function disableLogging()
    {
    }

    /**
     * {@inheritdoc}
     */
    protected function enableLogging($logger)
    {
    }

    /**
     * {@inheritdoc}
     */
    protected function configureOptions()
    {
        parent::configureOptions();

        $this->resolver->setDefaults([
            'clear_object_manager' => true,
            'debug_logging' => false,
            'ignore_errors' => false,
            'offset' => 0,
            'query_builder_method' => 'createQueryBuilder',
            'sleep' => 0,
        ]);
    }
}
