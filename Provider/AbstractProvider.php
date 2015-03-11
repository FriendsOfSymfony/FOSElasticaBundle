<?php

namespace FOS\ElasticaBundle\Provider;

use FOS\ElasticaBundle\Persister\ObjectPersisterInterface;

/**
 * AbstractProvider
 */
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
     * @var Indexable
     */
    private $indexable;

    /**
     * Constructor.
     *
     * @param ObjectPersisterInterface $objectPersister
     * @param IndexableInterface $indexable
     * @param string $objectClass
     * @param array $options
     */
    public function __construct(
        ObjectPersisterInterface $objectPersister,
        IndexableInterface $indexable,
        $objectClass,
        array $options = array()
    ) {
        $this->indexable = $indexable;
        $this->objectClass = $objectClass;
        $this->objectPersister = $objectPersister;

        $this->options = array_merge(array(
            'batch_size' => 100,
        ), $options);
    }

    /**
     * Checks if a given object should be indexed or not.
     *
     * @param object $object
     * @return bool
     */
    protected function isObjectIndexable($object)
    {
        return $this->indexable->isObjectIndexable(
            $this->options['indexName'],
            $this->options['typeName'],
            $object
        );
    }

    /**
     * Get string with RAM usage information (current and peak)
     *
     * @deprecated To be removed in 4.0
     * @return string
     */
    protected function getMemoryUsage()
    {
        $memory = round(memory_get_usage() / (1024 * 1024)); // to get usage in Mo
        $memoryMax = round(memory_get_peak_usage() / (1024 * 1024)); // to get max usage in Mo

        return sprintf('(RAM : current=%uMo peak=%uMo)', $memory, $memoryMax);
    }
}
