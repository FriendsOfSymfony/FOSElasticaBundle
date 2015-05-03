<?php

namespace FOS\ElasticaBundle\Doctrine;

use Doctrine\Common\Persistence\ManagerRegistry;
use FOS\ElasticaBundle\HybridResult;
use FOS\ElasticaBundle\Transformer\AbstractElasticaToModelTransformer as BaseTransformer;
use FOS\ElasticaBundle\Transformer\HighlightableModelInterface;

/**
 * Maps Elastica documents with Doctrine objects
 * This mapper assumes an exact match between
 * elastica documents ids and doctrine object ids.
 */
abstract class AbstractElasticaToModelTransformer extends BaseTransformer
{
    /**
     * Manager registry.
     *
     * @var ManagerRegistry
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
     * @param ManagerRegistry $registry
     * @param string $objectClass
     * @param array  $options
     */
    public function __construct(ManagerRegistry $registry, $objectClass, array $options = array())
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
     * @param array $options of transform options
     *
     * @throws \RuntimeException
     *
     * @return array
     **/
    public function transform(array $elasticaObjects, array $options = array())
    {
        $options = array_merge($this->options, $options);
        $ids = $highlights = array();
        foreach ($elasticaObjects as $elasticaObject) {
            $ids[] = $elasticaObject->getId();
            $highlights[$elasticaObject->getId()] = $elasticaObject->getHighlights();
        }

        $objects = $this->findByIdentifiers($ids, $options);
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
        $identifier = $options['identifier'];

        if ($options['hydrate'] === false) {
            $identifier = '[' . $identifier . ']';
        }

        usort($objects, $this->getSortingClosure($idPos, $identifier));

        return $objects;
    }

    public function hybridTransform(array $elasticaObjects, array $options = array())
    {
        $options = array_merge($this->options, $options);
        $indexedElasticaResults = array();
        foreach ($elasticaObjects as $elasticaObject) {
            $indexedElasticaResults[$elasticaObject->getId()] = $elasticaObject;
        }

        $objects = $this->transform($elasticaObjects, $options);

        $result = array();
        foreach ($objects as $object) {
            $id = $this->propertyAccessor->getValue($object, $options['identifier']);
            $result[] = new HybridResult($indexedElasticaResults[$id], $object);
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function getIdentifierField()
    {
        return $this->options['identifier'];
    }

    /**
     * Fetches objects by theses identifier values.
     *
     * @param array $identifierValues ids values
     * @param array $options transform options
     *
     * @return array of objects or arrays
     */
    abstract protected function findByIdentifiers(array $identifierValues, array $options = array());
}
