<?php

namespace FOQ\ElasticaBundle;

use Elastica_Type;
use InvalidArgumentException;

/**
 * Stores the configured mappings for all types
 * Responsible for applying configured mappings to elastica types
 */
class MappingRegistry
{
    /**
     * Configured mappings. See http://www.elasticsearch.org/guide/reference/mapping/
     * array(
     *   "index_name/type_name" => array(type_object, mapping_array)
     * )
     *
     * @var array
     */
    protected $mappings = null;

    /**
     * Instanciates a new MappingSetter
     *
     * @param array mappings
     */
    public function __construct($mappings)
    {
        $this->mappings = $mappings;
    }

    /**
     * Apply mappings to all elastica types
     **/
    public function applyMappings()
    {
        foreach ($this->mappings as $pair) {
            list($type, $mappings) = $pair;
            $type->setMapping($mappings);
        }
    }

    /**
     * Gets the type mapping field names
     *
     * @param Elastica_Type $type
     * @return array list of fields names
     */
    public function getTypeFieldNames(Elastica_Type $type)
    {
        $key = sprintf('%s/%s', $type->getIndex()->getName(), $type->getType());
        if (!isset($this->mappings[$key])) {
            throw new InvalidArgumentException(sprintf('This type is not registered: "%s".', $key));
        }

        return array_keys($this->mappings[$key][1]);
    }
}
