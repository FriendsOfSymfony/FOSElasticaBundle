<?php

namespace FOS\ElasticaBundle\Type;

/**
 * A data object that contains configuration information about a specific type.
 *
 * @author Tim Nagel <tim@nagel.com.au>
 */
final class TypeConfiguration
{
    /**
     * The identifier property that is used to retrieve an identifier from the model.
     *
     * @var string
     */
    private $identifierProperty;

    /**
     * Returns the fully qualified class for the model that this type represents.
     *
     * @var string
     */
    private $modelClass;

    /**
     * Returns the repository method that will create a query builder or associated
     * query object for lookup purposes.
     *
     * @var string
     */
    private $repositoryMethod;

    /**
     * Returns the name of the type.
     *
     * @var string
     */
    private $type;

    /**
     * If the lookup should hydrate models to objects or leave data as an array.
     *
     * @var bool
     */
    private $hydrate = true;

    /**
     * If the type should ignore missing results from a lookup.
     *
     * @var bool
     */
    private $ignoreMissing = false;

    /**
     * @return boolean
     */
    public function isHydrate()
    {
        return $this->hydrate;
    }

    /**
     * @return string
     */
    public function getIdentifierProperty()
    {
        return $this->identifierProperty;
    }

    /**
     * @return boolean
     */
    public function isIgnoreMissing()
    {
        return $this->ignoreMissing;
    }

    /**
     * @return string
     */
    public function getModelClass()
    {
        return $this->modelClass;
    }

    /**
     * @return string
     */
    public function getRepositoryMethod()
    {
        return $this->repositoryMethod;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }
}
