<?php

namespace FOS\ElasticaBundle\Provider;

use FOS\ElasticaBundle\Persister\ObjectPersisterInterface;

abstract class AbstractProvider implements ProviderInterface
{
    /**
     * @var ObjectPersisterInterface
     */
    protected $objectPersister;

    /**
     * @var string
     */
    protected $objectClass;

    /**
     * @var array
     */
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
            'batch_size'      => 100,
            'disable_logging' => false,
            'ignore_errors'   => false,
        ), $options);
    }

    /**
     * Get string with RAM usage information (current and peak)
     *
     * @return string
     */
    protected function getMemoryUsage()
    {
        $memory = round(memory_get_usage() / (1024 * 1024)); // to get usage in Mo
        $memoryMax = round(memory_get_peak_usage() / (1024 * 1024)); // to get max usage in Mo

        return sprintf('(RAM : current=%uMo peak=%uMo)', $memory, $memoryMax);
    }
}
