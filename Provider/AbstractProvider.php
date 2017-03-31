<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
        array $baseOptions = []
    ) {
        $this->baseOptions = $baseOptions;
        $this->indexable = $indexable;
        $this->objectClass = $objectClass;
        $this->objectPersister = $objectPersister;
        $this->resolver = new OptionsResolver();
        $this->configureOptions();
    }

    /**
     * {@inheritdoc}
     */
    public function populate(\Closure $loggerClosure = null, array $options = [])
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
     * @param array    $options
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
     * Configures the option resolver.
     */
    protected function configureOptions()
    {
        $this->resolver->setDefaults([
            'reset' => true,
            'delete' => true,
            'batch_size' => 100,
            'skip_indexable_check' => false,
        ]);

        $this->resolver->setRequired([
            'indexName',
            'typeName',
        ]);
    }

    /**
     * Filters objects away if they are not indexable.
     *
     * @param array $options
     * @param array $objects
     *
     * @return array
     */
    protected function filterObjects(array $options, array $objects)
    {
        if ($options['skip_indexable_check']) {
            return $objects;
        }

        $index = $options['indexName'];
        $type = $options['typeName'];

        $return = [];
        foreach ($objects as $object) {
            if (!$this->indexable->isObjectIndexable($index, $type, $object)) {
                continue;
            }

            $return[] = $object;
        }

        return $return;
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
