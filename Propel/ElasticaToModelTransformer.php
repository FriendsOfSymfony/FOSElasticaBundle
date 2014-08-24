<?php

namespace FOS\ElasticaBundle\Propel;

use FOS\ElasticaBundle\HybridResult;
use FOS\ElasticaBundle\Transformer\ElasticaToModelTransformerInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * Maps Elastica documents with Propel objects.
 *
 * This mapper assumes an exact match between Elastica document IDs and Propel
 * entity IDs.
 *
 * @author William Durand <william.durand1@gmail.com>
 */
class ElasticaToModelTransformer implements ElasticaToModelTransformerInterface
{
    /**
     * Propel model class to map to Elastica documents.
     *
     * @var string
     */
    protected $objectClass = null;

    /**
     * Transformer options.
     *
     * @var array
     */
    protected $options = array(
        'hydrate'    => true,
        'identifier' => 'id',
    );

    /**
     * PropertyAccessor instance.
     *
     * @var PropertyAccessorInterface
     */
    protected $propertyAccessor;

    /**
     * Constructor.
     *
     * @param string $objectClass
     * @param array $options
     */
    public function __construct($objectClass, array $options = array())
    {
        $this->objectClass = $objectClass;
        $this->options = array_merge($this->options, $options);
    }

    /**
     * Set the PropertyAccessor instance.
     *
     * @param PropertyAccessorInterface $propertyAccessor
     */
    public function setPropertyAccessor(PropertyAccessorInterface $propertyAccessor)
    {
        $this->propertyAccessor = $propertyAccessor;
    }

    /**
     * Transforms an array of Elastica document into an array of Propel entities
     * fetched from the database.
     *
     * @param array $elasticaObjects
     * @return array|\ArrayObject
     */
    public function transform(array $elasticaObjects)
    {
        $ids = array();
        foreach ($elasticaObjects as $elasticaObject) {
            $ids[] = $elasticaObject->getId();
        }

        $objects = $this->findByIdentifiers($ids, $this->options['hydrate']);

        // Sort objects in the order of their IDs
        $idPos = array_flip($ids);
        $identifier = $this->options['identifier'];
        $propertyAccessor = $this->propertyAccessor;

        $sortCallback = function($a, $b) use ($idPos, $identifier, $propertyAccessor) {
            return $idPos[$propertyAccessor->getValue($a, $identifier)] > $idPos[$propertyAccessor->getValue($b, $identifier)];
        };

        if (is_object($objects)) {
            $objects->uasort($sortCallback);
        } else {
            usort($objects, $sortCallback);
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
     * Fetch Propel entities for the given identifier values.
     *
     * If $hydrate is false, the returned array elements will be arrays.
     * Otherwise, the results will be hydrated to instances of the model class.
     *
     * @param array   $identifierValues Identifier values
     * @param boolean $hydrate          Whether or not to hydrate the results
     * @return array
     */
    protected function findByIdentifiers(array $identifierValues, $hydrate)
    {
        if (empty($identifierValues)) {
            return array();
        }

        $query = $this->createQuery($this->objectClass, $this->options['identifier'], $identifierValues);

        if ( ! $hydrate) {
            return $query->toArray();
        }

        return $query->find();
    }

    /**
     * Create a query to use in the findByIdentifiers() method.
     *
     * @param string $class            Propel model class
     * @param string $identifierField  Identifier field name (e.g. "id")
     * @param array  $identifierValues Identifier values
     * @return \ModelCriteria
     */
    protected function createQuery($class, $identifierField, array $identifierValues)
    {
        $queryClass   = $class.'Query';
        $filterMethod = 'filterBy'.$this->camelize($identifierField);

        return $queryClass::create()->$filterMethod($identifierValues);
    }

    /**
     * @see https://github.com/doctrine/common/blob/master/lib/Doctrine/Common/Util/Inflector.php
     * @param string $str
     */
    private function camelize($str)
    {
        return ucfirst(str_replace(" ", "", ucwords(strtr($str, "_-", "  "))));
    }
}
