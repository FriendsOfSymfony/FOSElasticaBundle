<?php

namespace FOQ\ElasticaBundle\Doctrine;

use FOQ\ElasticaBundle\HybridResult;
use FOQ\ElasticaBundle\Transformer\ElasticaToModelTransformerInterface;
use Elastica_Document;

/**
 * Maps Elastica documents with Doctrine objects
 * This mapper assumes an exact match between
 * elastica documents ids and doctrine object ids
 */
abstract class AbstractElasticaToModelTransformer implements ElasticaToModelTransformerInterface
{
    /**
     * Manager registry
     */
    protected $registry = null;

    /**
     * Class of the model to map to the elastica documents
     *
     * @var string
     */
    protected $objectClass = null;

    /**
     * Optional parameters
     *
     * @var array
     */
    protected $options = array(
        'hydrate'    => true,
		'identifier' => 'id'
    );

    /**
     * Instantiates a new Mapper
     *
     * @param object $registry
     * @param string $objectClass
     * @param array $options
     */
    public function __construct($registry, $objectClass, array $options = array())
    {
        $this->registry    = $registry;
        $this->objectClass = $objectClass;
        $this->options     = array_merge($this->options, $options);
    }

    /**
     * Returns the object class that is used for conversion.
     *
     * @return string
     */
    public function getObjectClass()
    {
        return $this->objectClass;
    }

    /**
     * Transforms an array of elastica objects into an array of
     * model objects fetched from the doctrine repository
     *
     * @param array of elastica objects
     * @return array
     **/
    public function transform(array $elasticaObjects)
    {
        $ids = array_map(function($elasticaObject) {
            return $elasticaObject->getId();
        }, $elasticaObjects);
        $objects = $this->findByIdentifiers($this->objectClass, $this->options['identifier'], $ids, $this->options['hydrate']);
        if (count($objects) < count($elasticaObjects)) {
            throw new \RuntimeException('Cannot find corresponding Doctrine objects for all Elastica results.');
        };

        $identifierGetter = 'get'.ucfirst($this->options['identifier']);

        // sort objects in the order of ids
        $idPos = array_flip($ids);
        usort($objects, function($a, $b) use ($idPos, $identifierGetter)
        {
            return $idPos[$a->$identifierGetter()] > $idPos[$b->$identifierGetter()];
        });

        return $objects;
    }

    public function hybridTransform(array $elasticaObjects)
    {
        $objects = $this->transform($elasticaObjects);

        $result = array();
        for ($i = 0; $i < count($elasticaObjects); $i++) {
            $result[] = new HybridResult($elasticaObjects[$i], $objects[$i]);
        }

        return $result;
    }

    /**
     * Fetches objects by theses identifier values
     *
     * @param string $class the model class
     * @param string $identifierField like 'id'
     * @param array $identifierValues ids values
     * @param Boolean $hydrate whether or not to hydrate the objects, false returns arrays
     * @return array of objects or arrays
     */
    protected abstract function findByIdentifiers($class, $identifierField, array $identifierValues, $hydrate);
}
