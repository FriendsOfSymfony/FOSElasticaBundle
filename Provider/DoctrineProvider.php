<?php

namespace FOQ\ElasticaBundle\Provider;

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
    protected $options = array(
        'batch_size' => 100,
        'clear_object_manager' => true
    );

    public function __construct(Elastica_Type $type,  ObjectManager $objectManager, $objectClass, array $options = array())
    {
        $this->type          = $type;
        $this->objectManager = $objectManager;
        $this->objectClass   = $objectClass;
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
        $getters      = $this->buildGetters();

        for ($offset = 0; $offset < $nbObjects; $offset += $this->options['batch_size']) {

            $loggerClosure(sprintf('%0.1f%% (%d/%d)', 100*$offset/$nbObjects, $offset, $nbObjects));

            $this->type->addDocuments(array_map(function($object) use ($getters) {
                return new Elastica_Document($object->getId(), array_map(function($getter) use ($object) {
                    return $object->$getter();
                }, $getters));
            }, $queryBuilder->limit($this->options['batch_size'])->skip($offset)->getQuery()->execute()->toArray()));

            if ($this->options['clear_object_manager']) {
                $this->objectManager->clear();
            }
        }
    }

    /**
     * Preprocesses getters based on the type mappings
     *
     * @return null
     **/
    public function buildGetters()
    {
        $getters = array();
        $mappings = $this->type->getMapping();
        // skip index name
        $mappings = reset($mappings);
        // skip type name
        $mappings = reset($mappings);
        $mappings = $mappings['properties'];
        foreach ($mappings as $property => $mappingOptions) {
            $getter = 'get'.ucfirst($property);
            if (!method_exists($this->objectClass, $getter)) {
                throw new InvalidArgumentException(sprintf('The getter %s::%s does not exist', $this->objectClass, $getter));
            }
            $getters[$property] = $getter;
        }

        return $getters;
    }

    /**
     * Creates the query buider used to fetch the documents to index
     *
     * @return Query
     **/
    protected function createQueryBuilder()
    {
        return $this->objectManager->createQueryBuilder($this->objectClass);
    }
}
