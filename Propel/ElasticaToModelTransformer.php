<?php

namespace FOQ\ElasticaBundle\Propel;

use FOQ\ElasticaBundle\HybridResult;
use FOQ\ElasticaBundle\Transformer\ElasticaToModelTransformerInterface;
use Elastica_Document;
use Symfony\Component\Form\Util\PropertyPath;

/**
 * Maps Elastica documents with Propel objects
 * This mapper assumes an exact match between
 * elastica documents ids and propel object ids
 *
 * @author William Durand <william.durand1@gmail.com>
 */
class ElasticaToModelTransformer implements ElasticaToModelTransformerInterface
{
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
     * @param string $objectClass
     * @param array $options
     */
    public function __construct($objectClass, array $options = array())
    {
        $this->objectClass = $objectClass;
        $this->options     = array_merge($this->options, $options);
    }

    /**
     * Transforms an array of elastica objects into an array of
     * model objects fetched from the propel repository
     *
     * @param array of elastica objects
     * @return array
     */
    public function transform(array $elasticaObjects)
    {
        $ids = array_map(function($elasticaObject) {
            return $elasticaObject->getId();
        }, $elasticaObjects);

        $objects = $this->findByIdentifiers($this->objectClass, $this->options['identifier'], $ids, $this->options['hydrate']);

        $identifierProperty =  new PropertyPath($this->options['identifier']);

        // sort objects in the order of ids
        $idPos = array_flip($ids);
        if (is_object($objects)) {
            $objects->uasort(function($a, $b) use ($idPos, $identifierProperty) {
                return $idPos[$identifierProperty->getValue($a)] > $idPos[$identifierProperty->getValue($b)];
            });
        } else {
            usort($objects, function($a, $b) use ($idPos, $identifierProperty) {
                return $idPos[$identifierProperty->getValue($a)] > $idPos[$identifierProperty->getValue($b)];
            });
        }

        return $objects;
    }

    /**
     * {@inheritdoc}
     */
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
     * {@inheritdoc}
     */
    public function getObjectClass()
    {
        return $this->objectClass;
    }

    /**
     * Fetch objects for theses identifier values
     *
     * @param string $class the model class
     * @param string $identifierField like 'id'
     * @param array $identifierValues ids values
     * @param Boolean $hydrate whether or not to hydrate the objects, false returns arrays
     * @return array of objects or arrays
     */
    protected function findByIdentifiers($class, $identifierField, array $identifierValues, $hydrate)
    {
        if (empty($identifierValues)) {
            return array();
        }

        $query = $this->createQuery($class, $identifierField, $identifierValues);

        if (!$hydrate) {
            return $query->toArray();
        }

        return $query->find();
    }

    /**
     * Create a query to use in the findByIdentifiers() method.
     *
     * @param string $class the model class
     * @param string $identifierField like 'id'
     * @param array $identifierValues ids values
     * @return \ModelCriteria
     */
    protected function createQuery($class, $identifierField, array $identifierValues)
    {
        $queryClass   = $class.'Query';
        $filterMethod = 'filterBy'.$this->camelize($identifierField);

        return $queryClass::create()
            ->$filterMethod($identifierValues)
            ;
    }

    /**
     * @see https://github.com/doctrine/common/blob/master/lib/Doctrine/Common/Util/Inflector.php
     */
    private function camelize($str)
    {
        return ucfirst(str_replace(" ", "", ucwords(strtr($str, "_-", "  "))));
    }
}
