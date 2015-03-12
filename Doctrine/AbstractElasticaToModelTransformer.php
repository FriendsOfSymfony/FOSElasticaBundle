<?php

namespace FOS\ElasticaBundle\Doctrine;

use FOS\ElasticaBundle\HybridResult;
use FOS\ElasticaBundle\Transformer\AbstractElasticaToModelTransformer as BaseTransformer;
use FOS\ElasticaBundle\Transformer\HighlightableModelInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * Maps Elastica documents with Doctrine objects
 * This mapper assumes an exact match between
 * elastica documents ids and doctrine object ids.
 */
abstract class AbstractElasticaToModelTransformer extends BaseTransformer
{
    /**
     * Manager registry.
     */
    protected $registry = null;

    /**
     * Class of the model to map to the elastica documents.
     *
     * @var string
     */
    protected $objectClass = null;

    /**
     * Optional parameters.
     *
     * @var array
     */
    protected $options = array(
        'hydrate'        => true,
        'identifier'     => 'id',
        'ignore_missing' => false,
        'query_builder_method' => 'createQueryBuilder',
    );

    /**
     * Instantiates a new Mapper.
     *
     * @param object $registry
     * @param string $objectClass
     * @param array  $options
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
     * model objects fetched from the doctrine repository.
     *
     * @param array $elasticaObjects of elastica objects
     *
     * @throws \RuntimeException
     *
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
        if (!$this->options['ignore_missing'] && count($objects) < count($elasticaObjects)) {
            throw new \RuntimeException('Cannot find corresponding Doctrine objects for all Elastica results.');
        };

        foreach ($objects as $object) {
            if ($object instanceof HighlightableModelInterface) {
                $object->setElasticHighlights($highlights[$object->getId()]);
            }
        }

        // sort objects in the order of ids
        $idPos = array_flip($ids);
        $identifier = $this->options['identifier'];
        usort($objects, $this->getSortingClosure($idPos, $identifier));

        return $objects;
    }

    public function hybridTransform(array $elasticaObjects)
    {
        $indexedElasticaResults = array();
        foreach ($elasticaObjects as $elasticaObject) {
            $indexedElasticaResults[$elasticaObject->getId()] = $elasticaObject;
        }

        $objects = $this->transform($elasticaObjects);

        $result = array();
        foreach ($objects as $object) {
            $id = $this->propertyAccessor->getValue($object, $this->options['identifier']);
            $result[] = new HybridResult($indexedElasticaResults[$id], $object);
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
     * Fetches objects by theses identifier values.
     *
     * @param array   $identifierValues ids values
     * @param Boolean $hydrate          whether or not to hydrate the objects, false returns arrays
     *
     * @return array of objects or arrays
     */
    abstract protected function findByIdentifiers(array $identifierValues, $hydrate);
}
