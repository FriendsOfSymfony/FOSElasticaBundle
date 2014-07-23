<?php

/**
 * This file is part of the FOSElasticaBundle project.
 *
 * (c) Infinite Networks Pty Ltd <http://www.infinite.net.au>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Index;

use FOS\ElasticaBundle\Configuration\IndexConfig;
use FOS\ElasticaBundle\Configuration\TypeConfig;

class MappingBuilder
{
    /**
     * Builds mappings for an entire index.
     *
     * @param IndexConfig $indexConfig
     * @return array
     */
    public function buildIndexMapping(IndexConfig $indexConfig)
    {
        $typeMappings = array();
        foreach ($indexConfig->getTypes() as $typeConfig) {
            $typeMappings[$typeConfig->getName()] = $this->buildTypeMapping($typeConfig);
        }

        $mapping = array(
            'mappings' => $typeMappings,
            // 'warmers' => $indexConfig->getWarmers(),
        );

        $settings = $indexConfig->getSettings();
        if ($settings) {
            $mapping['settings'] = $settings;
        }

        return $mapping;
    }

    /**
     * Builds mappings for a single type.
     *
     * @param TypeConfig $typeConfig
     * @return array
     */
    public function buildTypeMapping(TypeConfig $typeConfig)
    {
        $mapping = array_merge($typeConfig->getMapping(), array(
            // 'date_detection' => true,
            // 'dynamic_date_formats' => array()
            // 'dynamic_templates' => $typeConfig->getDynamicTemplates(),
            // 'index_analyzer' => $typeConfig->getIndexAnalyzer(),
            // 'numeric_detection' => false,
            // 'properties' => array(),
            // 'search_analyzer' => $typeConfig->getSearchAnalyzer(),
        ));

        if (isset($mapping['dynamic_templates']) and empty($mapping['dynamic_templates'])) {
            unset($mapping['dynamic_templates']);
        }

        $this->fixProperties($mapping['properties']);

        if ($typeConfig->getModel()) {
            $mapping['_meta']['model'] = $typeConfig->getModel();
        }

        return $mapping;
    }

    /**
     * Fixes any properties and applies basic defaults for any field that does not have
     * required options.
     *
     * @param $properties
     */
    private function fixProperties(&$properties)
    {
        foreach ($properties as $name => &$property) {
            if (!isset($property['type'])) {
                $property['type'] = 'string';
            }
            if (!isset($property['store'])) {
                $property['store'] = true;
            }
            if ($property['store'] == "unset") {
                unset($property['store']);
            }
            if (isset($property['properties'])) {
                $this->fixProperties($property['properties']);
            }
        }
    }
}
