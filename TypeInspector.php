<?php

namespace FOQ\ElasticaBundle;

use Elastica_Type;

/**
 * Extracts the mapping fields from a type
 *
 * @author Thibault Duplessis <thibault.duplessis@gmail.com>
 */
class TypeInspector
{
    /**
     * Gets the type mapping fields
     *
     * @param Elastica_Type $type
     * @return array list of fields names
     */
    public function getMappingFieldsNames(Elastica_Type $type)
    {
        $mappings = $type->getMapping();
        // skip index and type name
        // < 0.16.0 has both index and type levels
        // >= 0.16.0 has only type level
        do {
            $mappings = reset($mappings);
        } while (is_array($mappings) && !isset($mappings['properties']));
        if (!isset($mappings['properties'])) {
            return array();
        }
        $mappings = $mappings['properties'];
        if (array_key_exists('__isInitialized__', $mappings)) {
            unset($mappings['__isInitialized__']);
        }

        return array_keys($mappings);
    }
}
