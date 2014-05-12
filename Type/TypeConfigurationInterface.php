<?php

namespace FOS\ElasticaBundle\Type;

/**
 * A data object that contains configuration information about a specific type.
 *
 * @author Tim Nagel <tim@nagel.com.au>
 */
interface TypeConfigurationInterface
{
    /**
     * The identifier property that is used to retrieve an identifier from the model.
     *
     * @return string
     */
    public function getIdentifierProperty();

    /**
     * Returns the fully qualified class for the model that this type represents.
     *
     * @return string
     */
    public function getModelClass();

    /**
     * Returns the repository method that will create a query builder or associated
     * query object for lookup purposes.
     *
     * @return string
     */
    public function getRepositoryMethod();

    /**
     * Returns the name of the type.
     *
     * @return string
     */
    public function getType();

    /**
     * If the lookup should hydrate models to objects or leave data as an array.
     *
     * @return bool
     */
    public function isHydrate();

    /**
     * If the type should ignore missing results from a lookup.
     *
     * @return bool
     */
    public function isIgnoreMissing();
}