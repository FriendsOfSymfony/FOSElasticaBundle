<?php

namespace FOS\ElasticaBundle\Propel;

use FOS\ElasticaBundle\HybridResult;
use FOS\ElasticaBundle\Transformer\ElasticaToModelTransformerInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

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
     * PropertyAccessor instance
     *
     * @var PropertyAccessorInterface
     */
    protected $propertyAccessor;

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
     * Set the PropertyAccessor
     *
     * @param PropertyAccessorInterface $propertyAccessor
     */
    public function setPropertyAccessor(PropertyAccessorInterface $propertyAccessor)
    {
        $this->propertyAccessor = $propertyAccessor;
    }

    /**
     * Transforms an array of elastica objects into an array of
     * model objects fetched from the propel repository
     *
     * @param Document[] $elasticaObjects array of elastica objects
     * @return array
     */
    public function transform(array $elasticaObjects)
    {
        $ids = array();
        foreach ($elasticaObjects as $elasticaObject) {
            $ids[] = $elasticaObject->getId();
        }

        $objects = $this->findByIdentifiers($ids, $this->options['hydrate']);

        // sort objects in the order of ids
        $idPos = array_flip($ids);
        $identifier = $this->options['identifier'];
        $propertyAccessor = $this->propertyAccessor;
        if (is_object($objects)) {
            $objects->uasort(function($a, $b) use ($idPos, $identifier, $propertyAccessor) {
                return $idPos[$propertyAccessor->getValue($a, $identifier)] > $idPos[$propertyAccessor->getValue($b, $identifier)];
            });
        } else {
            usort($objects, function($a, $b) use ($idPos, $identifier, $propertyAccessor) {
                return $idPos[$propertyAccessor->getValue($a, $identifier)] > $idPos[$propertyAccessor->getValue($b, $identifier)];
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
     * {@inheritdoc}
     */
    public function getIdentifierField()
    {
        return $this->options['identifier'];
    }

    /**
     * Fetch objects for theses identifier values
     *
     * @param array $identifierValues ids values
     * @param boolean $hydrate whether or not to hydrate the objects, false returns arrays
     * @return array of objects or arrays
     */
    protected function findByIdentifiers(array $identifierValues, $hydrate)
    {
        if (empty($identifierValues)) {
            return array();
        }

        $query = $this->createQuery($this->objectClass, $this->options['identifier'], $identifierValues);

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
