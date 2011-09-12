<?php

namespace FOQ\ElasticaBundle\Registry;

use Elastica_Type;
use InvalidArgumentException;

/**
 * Stores the configured mappings for all indexes
 * Responsible for applying configured mappings to elastica indexes
 */
class SettingRegistry
{
    /**
     * Configured settings.
     * array(
     *   "index_name" => array(index_object, setting_array)
     * )
     *
     * @var array
     */
    protected $settings;

    /**
     * Instanciates a new SettingSetter
     *
     * @param array settings
     */
    public function __construct(array $settings)
    {
        $this->settings = $settings;
    }

    /**
     * Apply settings to all elastica indexes
     **/
    public function applySettings()
    {
        foreach ($this->settings as $pair) {
            list($index, $settings) = $pair;
            $index->setSettings($settings);
        }
    }
}
