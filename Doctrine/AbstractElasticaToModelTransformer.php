<?php

namespace FOS\ElasticaBundle\Doctrine;

use FOS\ElasticaBundle\HybridResult;
use FOS\ElasticaBundle\Transformer\ElasticaToModelTransformerInterface;
use FOS\ElasticaBundle\Transformer\HighlightableModelInterface;
use Symfony\Component\Form\Util\PropertyPath;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

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
     * PropertyAccessor instance (will be used if available)
     *
     * @var PropertyAccessorInterface
     */
    private $propertyAccessor;

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
     * Set the PropertyAccessor
     *
     * @param PropertyAccessorInterface $propertyAccessor
     */
    public function setPropertyAccessor(PropertyAccessorInterface $propertyAccessor = null)
    {
        $this->propertyAccessor = $propertyAccessor;
    }

    /**
     * Transforms an array of elastica objects into an array of
     * model objects fetched from the doctrine repository
     *
     * @param array $elasticaObjects of elastica objects
     * @throws \RuntimeException
     * @return array
     **/
    public function transform(array $elasticaObjects)
    {
        $ids = $highlights = array();
        foreach ($elasticaObjects as $elasticaObject) {
            $ids[] = $elasticaObject->getId();
            $highlights[$elasticaObject->getId()] = $elasticaObject->getHighlights();
        }

        $objects = $this->findByIdentifiers($ids, $this->options['hydrate']);
        if (count($objects) < count($elasticaObjects)) {
            throw new \RuntimeException('Cannot find corresponding Doctrine objects for all Elastica results.');
        };

        foreach ($objects as $object) {
            if ($object instanceof HighlightableModelInterface) {
                $object->setElasticHighlights($highlights[$object->getId()]);
            }
        }

        $identifierProperty =  $this->options['identifier'];

        // sort objects in the order of ids
        $idPos = array_flip($ids);
        $self = $this;
        usort($objects, function($a, $b) use ($idPos, $identifierProperty, $self)
        {
            return $idPos[$self->getPropertyValue($a, $identifierProperty)] > $idPos[$self->getPropertyValue($b, $identifierProperty)];
        });

        return $objects;
    }

    /**
     * Get the value of an object property.
     *
     * This method will use Symfony 2.2's PropertyAccessor if it is available.
     *
     * @param object $object
     * @param string $property
     * @return mixed
     */
    public function getPropertyValue($object, $property)
    {
        if (isset($this->propertyAccessor)) {
            return $this->propertyAccessor->getValue($object, $property);
        }

        $propertyPath = new PropertyPath($property);

        return $propertyPath->getValue($object);
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
     * {@inheritdoc}
     */
    public function getIdentifierField()
    {
        return $this->options['identifier'];
    }

    /**
     * Fetches objects by theses identifier values
     *
     * @param array $identifierValues ids values
     * @param Boolean $hydrate whether or not to hydrate the objects, false returns arrays
     * @return array of objects or arrays
     */
    protected abstract function findByIdentifiers(array $identifierValues, $hydrate);
}
