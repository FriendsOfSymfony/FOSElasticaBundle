<?php

namespace FOQ\ElasticaBundle;

/**
 * Responsible for applying configured mappings to elastica types
 */
class MappingSetter
{
    /**
     * Configured mappings. See http://www.elasticsearch.org/guide/reference/mapping/
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
     *
     * @return null
     **/
    public function setMappings()
    {
        foreach ($this->mappings as $pair) {
            list($type, $mappings) = $pair;
            $type->setMapping($mappings);
        }
    }
}
