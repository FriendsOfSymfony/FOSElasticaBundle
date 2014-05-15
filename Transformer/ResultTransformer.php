<?php

namespace FOS\ElasticaBundle\Transformer;

use FOS\ElasticaBundle\Exception\MissingModelException;
use FOS\ElasticaBundle\Exception\UnexpectedObjectException;
use FOS\ElasticaBundle\Type\LookupManager;
use FOS\ElasticaBundle\Type\TypeConfiguration;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * Handles transforming results into models.
 */
class ResultTransformer implements ResultTransformerInterface
{
    /**
     * @var \FOS\ElasticaBundle\Type\LookupManager
     */
    private $lookupManager;

    /**
     * @var \Symfony\Component\PropertyAccess\PropertyAccessorInterface
     */
    private $propertyAccessor;

    public function __construct(
        LookupManager $lookupManager,
        PropertyAccessorInterface $propertyAccessor
    ) {
        $this->lookupManager = $lookupManager;
        $this->propertyAccessor = $propertyAccessor;
    }

    /**
     * Transforms Elastica results into Models.
     *
     * @param TypeConfiguration $configuration
     * @param \FOS\ElasticaBundle\Elastica\TransformingResult[] $results
     * @throws \FOS\ElasticaBundle\Exception\MissingModelException
     * @throws \FOS\ElasticaBundle\Exception\UnexpectedObjectException
     */
    public function transform(TypeConfiguration $configuration, $results)
    {
        $results = $this->processResults($results);
        $lookup = $this->lookupManager->getLookup($configuration->getType());
        $objects = $lookup->lookup($configuration, array_keys($results));

        if (!$configuration->isIgnoreMissing() and count($objects) < count($results)) {
            throw new MissingModelException(count($objects), count($results));
        }

        $identifierProperty = $configuration->getIdentifierProperty();
        foreach ($objects as $object) {
            $id = $this->propertyAccessor->getValue($object, $identifierProperty);

            if (!array_key_exists($id, $results)) {
                throw new UnexpectedObjectException($id);
            }

            $results[$id]->setTransformed($object);
        }
    }

    /**
     * Processes the results array into a more usable format for the transformation.
     *
     * @param \FOS\ElasticaBundle\Elastica\TransformingResult[] $results
     * @return \FOS\ElasticaBundle\Elastica\TransformingResult[]
     */
    private function processResults($results)
    {
        $sorted = array();
        foreach ($results as $result) {
            $sorted[$result->getId()] = $result;
        }

        return $sorted;
    }
}
