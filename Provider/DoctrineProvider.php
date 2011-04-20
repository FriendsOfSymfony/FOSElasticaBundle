<?php

namespace FOQ\ElasticaBundle\Provider;

use FOQ\ElasticaBundle\Transformer\ObjectToArrayTransformerInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Elastica_Type;
use Elastica_Document;
use Closure;
use InvalidArgumentException;

class DoctrineProvider implements ProviderInterface
{
    protected $type;
    protected $objectManager;
    protected $objectClass;
    protected $transformer;
    protected $options = array(
        'batch_size'           => 100,
        'clear_object_manager' => true,
		'query_builder_method' => 'createQueryBuilder',
		'identifier'           => 'id'
    );

    public function __construct(Elastica_Type $type,  ObjectManager $objectManager, ObjectToArrayTransformerInterface $transformer, $objectClass, array $options = array())
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
		$identifierGetter = 'get'.ucfirst($this->options['identifier']);

        for ($offset = 0; $offset < $nbObjects; $offset += $this->options['batch_size']) {

            $documents = array();
            $objects = $queryBuilder->limit($this->options['batch_size'])->skip($offset)->getQuery()->execute()->toArray();
            $stepCount = (count($objects)+$offset);
            $loggerClosure(sprintf('%0.1f%% (%d/%d)', 100*$stepCount/$nbObjects, $stepCount, $nbObjects));

            foreach ($objects as $object) {
                $data = $this->transformer->transform($object, $fields);
                $documents[] = new Elastica_Document($object->$identifierGetter(), $data);
            }
            $this->type->addDocuments($documents);

            if ($this->options['clear_object_manager']) {
                $this->objectManager->clear();
            }
        }
    }

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
