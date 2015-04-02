<?php

namespace FOS\ElasticaBundle\Propel;

use FOS\ElasticaBundle\HybridResult;
use FOS\ElasticaBundle\Transformer\AbstractElasticaToModelTransformer;
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
class ElasticaToModelTransformer extends AbstractElasticaToModelTransformer
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
     * Constructor.
     *
     * @param string $objectClass
     * @param array  $options
     */
    public function __construct($objectClass, array $options = array())
    {
        $this->objectClass = $objectClass;
        $this->options = array_merge($this->options, $options);
    }

    /**
     * Transforms an array of Elastica document into an array of Propel entities
     * fetched from the database.
     *
     * @param array $elasticaObjects
     * @param array $options
     *
     * @return array|\ArrayObject
     */
    public function transform(array $elasticaObjects, array $options = array())
    {
        $options = array_merge($this->options, $options);

        $ids = array();
        foreach ($elasticaObjects as $elasticaObject) {
            $ids[] = $elasticaObject->getId();
        }

        $objects = $this->findByIdentifiers($ids, $options['hydrate']);

        // Sort objects in the order of their IDs
        $idPos = array_flip($ids);
        $identifier = $options['identifier'];
        $sortCallback = $this->getSortingClosure($idPos, $identifier);

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
    public function hybridTransform(array $elasticaObjects, array $options = array())
    {
        $options = array_merge($this->options, $options);

        $objects = $this->transform($elasticaObjects, $options);

        $result = array();
        for ($i = 0, $j = count($elasticaObjects); $i < $j; $i++) {
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
     * @param array $identifierValues Identifier values
     * @param array $options transform options
     *
     * @return array
     */
    protected function findByIdentifiers(array $identifierValues, array $options = array())
    {
        $options = array_merge($this->options, $options);

        if (empty($identifierValues)) {
            return array();
        }

        $query = $this->createQuery($this->objectClass, $options['identifier'], $identifierValues);

        if (!$options['hydrate']) {
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
     *
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
     *
     * @param string $str
     */
    private function camelize($str)
    {
        return ucfirst(str_replace(" ", "", ucwords(strtr($str, "_-", "  "))));
    }
}
