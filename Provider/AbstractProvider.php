<?php

namespace FOQ\ElasticaBundle\Provider;

use FOQ\ElasticaBundle\Persister\ObjectPersisterInterface;
use FOQ\ElasticaBundle\Provider\ProviderInterface;

abstract class AbstractProvider implements ProviderInterface
{
    protected $objectClass;
    protected $objectPersister;
    protected $options;

    /**
     * Constructor.
     *
     * @param ObjectPersisterInterface $objectPersister
     * @param string                   $objectClass
     * @param array                    $options
     */
    public function __construct(ObjectPersisterInterface $objectPersister, $objectClass, array $options = array())
    {
        $this->objectPersister = $objectPersister;
        $this->objectClass = $objectClass;

        $this->options = array_merge(array(
            'batch_size' => 100,
        ), $options);
    }
}
