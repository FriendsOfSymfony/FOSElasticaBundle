<?php

namespace FOQ\ElasticaBundle\Provider;

use FOQ\ElasticaBundle\Transformer\ModelToElasticaTransformerInterface;
use Elastica_Type;
use Elastica_Document;
use Closure;
use InvalidArgumentException;

abstract class AbstractDoctrineProvider implements ProviderInterface
{
    protected $type;
    protected $objectManager;
    protected $objectClass;
    protected $transformer;
    protected $options = array(
        'batch_size'           => 100,
        'clear_object_manager' => true,
		'query_builder_method' => 'createQueryBuilder'
    );

    public function __construct(Elastica_Type $type, $objectManager, ModelToElasticaTransformerInterface $transformer, $objectClass, array $options = array())
    {
        $this->type          = $type;
        $this->objectManager = $objectManager;
        $this->objectClass   = $objectClass;
        $this->transformer   = $transformer;
        $this->options       = array_merge($this->options, $options);
    }

    /**
     * Insert the repository objects in the type index
     *
     * @param Closure $loggerClosure
     */
    public function populate(Closure $loggerClosure)
    {
        $queryBuilder = $this->createQueryBuilder();
        $nbObjects    = $queryBuilder->getQuery()->count();
        $fields       = $this->extractTypeFields();

        for ($offset = 0; $offset < $nbObjects; $offset += $this->options['batch_size']) {

            $stepStartTime = microtime(true);
            $documents = array();
            $objects = $queryBuilder->limit($this->options['batch_size'])->skip($offset)->getQuery()->execute()->toArray();

            foreach ($objects as $object) {
                try {
                    $documents[] = $this->transformer->transform($object, $fields);
                } catch (NotIndexableException $e) {
                    // skip document
                }
            }
            $this->type->addDocuments($documents);

            if ($this->options['clear_object_manager']) {
                $this->objectManager->clear();
            }

            $stepNbObjects = count($objects);
            $stepCount = $stepNbObjects+$offset;
            $objectsPerSecond = $stepNbObjects / (microtime(true) - $stepStartTime);
            $loggerClosure(sprintf('%0.1f%% (%d/%d), %d objects/s', 100*$stepCount/$nbObjects, $stepCount, $nbObjects, $objectsPerSecond));
        }
    }

    /**
     * Counts the objects of a query builder
     *
     * @return int
     **/
    protected abstract function countObjects($queryBuilder);

    /**
     * Creates the query buider used to fetch the documents to index
     *
     * @return Query
     **/
    protected function createQueryBuilder()
    {
        return $this->objectManager->getRepository($this->objectClass)->{$this->options['query_builder_method']}();
    }

    protected function extractTypeFields()
    {
        $mappings = $this->type->getMapping();
        // skip index name
        $mappings = reset($mappings);
        // skip type name
        $mappings = reset($mappings);
        $mappings = $mappings['properties'];
        if (array_key_exists('__isInitialized__', $mappings)) {
            unset($mappings['__isInitialized__']);
        }

        return array_keys($mappings);
    }
}
