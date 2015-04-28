<?php

namespace FOS\ElasticaBundle\Provider;

use FOS\ElasticaBundle\Persister\ObjectPersisterInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * AbstractProvider.
 */
abstract class AbstractProvider implements ProviderInterface
{
    /**
     * @var array
     */
    protected $baseOptions;

    /**
     * @var string
     */
    protected $objectClass;

    /**
     * @var ObjectPersisterInterface
     */
    protected $objectPersister;

    /**
     * @var OptionsResolver
     */
    protected $resolver;

    /**
     * @var IndexableInterface
     */
    private $indexable;

    /**
     * Constructor.
     *
     * @param ObjectPersisterInterface $objectPersister
     * @param IndexableInterface       $indexable
     * @param string                   $objectClass
     * @param array                    $baseOptions
     */
    public function __construct(
        ObjectPersisterInterface $objectPersister,
        IndexableInterface $indexable,
        $objectClass,
        array $baseOptions = array()
    ) {
        $this->baseOptions = $baseOptions;
        $this->indexable = $indexable;
        $this->objectClass = $objectClass;
        $this->objectPersister = $objectPersister;
        $this->resolver = new OptionsResolver();
        $this->configureOptions();
    }

    /**
     * {@inheritDoc}
     */
    public function populate(\Closure $loggerClosure = null, array $options = array())
    {
        $options = $this->resolveOptions($options);

        $logger = !$options['debug_logging'] ?
            $this->disableLogging() :
            null;

        $this->doPopulate($options, $loggerClosure);

        if (null !== $logger) {
            $this->enableLogging($logger);
        }
    }

    /**
     * Disables logging and returns the logger that was previously set.
     *
     * @return mixed
     */
    abstract protected function disableLogging();

    /**
     * Perform actual population.
     *
     * @param array $options
     * @param \Closure $loggerClosure
     */
    abstract protected function doPopulate($options, \Closure $loggerClosure = null);

    /**
     * Reenables the logger with the previously returned logger from disableLogging();.
     *
     * @param mixed $logger
     *
     * @return mixed
     */
    abstract protected function enableLogging($logger);

    /**
     * Configures the option resolver. Some of these options can be ignored if
     * implementing this class if they do not have any behaviour for a specific provider.
     */
    protected function configureOptions()
    {
        $this->resolver->setDefaults(array(
            'batch_size' => 100,
            'clear_object_manager' => true,
            'debug_logging' => false,
            'ignore_errors' => false,
            'offset' => 0,
            'query_builder_method' => 'createQueryBuilder',
            'skip_indexable_check' => false,
            'sleep' => 0
        ));
        $this->resolver->setRequired(array(
            'indexName',
            'typeName',
        ));
        $this->resolver->setAllowedTypes(array(
            'batch_size' => 'int',
            'clear_object_manager' => 'bool',
            'debug_logging' => 'bool',
            'ignore_errors' => 'bool',
            'indexName' => 'string',
            'typeName' => 'string',
            'offset' => 'int',
            'query_builder_method' => 'string',
            'skip_indexable_check' => 'bool',
            'sleep' => 'int'
        ));
    }

    /**
     * Filters objects away if they are not indexable.
     *
     * @param array $options
     * @param array $objects
     * @return array
     */
    protected function filterObjects(array $options, array $objects)
    {
        if ($options['skip_indexable_check']) {
            return $objects;
        }

        $index = $options['indexName'];
        $type = $options['typeName'];

        $return = array();
        foreach ($objects as $object) {
            if (!$this->indexable->isObjectIndexable($index, $type, $object)) {
                continue;
            }

            $return[] = $object;
        }

        return $return;
    }

    /**
     * Checks if a given object should be indexed or not.
     *
     * @deprecated To be removed in 4.0
     *
     * @param object $object
     *
     * @return bool
     */
    protected function isObjectIndexable($object)
    {
        return $this->indexable->isObjectIndexable(
            $this->baseOptions['indexName'],
            $this->baseOptions['typeName'],
            $object
        );
    }

    /**
     * Get string with RAM usage information (current and peak).
     *
     * @deprecated To be removed in 4.0
     *
     * @return string
     */
    protected function getMemoryUsage()
    {
        $memory = round(memory_get_usage() / (1024 * 1024)); // to get usage in Mo
        $memoryMax = round(memory_get_peak_usage() / (1024 * 1024)); // to get max usage in Mo

        return sprintf('(RAM : current=%uMo peak=%uMo)', $memory, $memoryMax);
    }

    /**
     * Merges the base options provided by the class with options passed to the populate
     * method and runs them through the resolver.
     *
     * @param array $options
     *
     * @return array
     */
    protected function resolveOptions(array $options)
    {
        return $this->resolver->resolve(array_merge($this->baseOptions, $options));
    }
}
