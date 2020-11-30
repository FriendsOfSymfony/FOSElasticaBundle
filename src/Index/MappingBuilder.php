<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <https://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Index;

use FOS\ElasticaBundle\Configuration\IndexConfigInterface;
use FOS\ElasticaBundle\Configuration\IndexTemplateConfig;
use FOS\ElasticaBundle\Event\PostMappingBuildEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class MappingBuilder
{
    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->dispatcher = $eventDispatcher;
    }

    /**
     * Builds mappings for an entire index.
     *
     * @return array
     */
    public function buildIndexMapping(IndexConfigInterface $indexConfig)
    {
        $mapping = $this->buildMapping($indexConfig->getModel(), $indexConfig);

        $this->dispatcher->dispatch(new PostMappingBuildEvent($indexConfig, $mapping));

        $mappingIndex = [];
        if (!empty($mapping)) {
            $mappingIndex['mappings'] = $mapping;
        }

        $settings = $indexConfig->getSettings();
        if (!empty($settings)) {
            $mappingIndex['settings'] = $settings;
        }

        return $mappingIndex;
    }

    /**
     * Builds mappings for an entire index template.
     *
     * @return array
     */
    public function buildIndexTemplateMapping(IndexTemplateConfig $indexTemplateConfig)
    {
        $mapping = $this->buildIndexMapping($indexTemplateConfig);
        $mapping['template'] = $indexTemplateConfig->getTemplate();

        return $mapping;
    }

    /**
     * Builds mappings for a single type.
     *
     * @return array
     */
    public function buildMapping(?string $model, IndexConfigInterface $indexConfig)
    {
        $mapping = $indexConfig->getMapping();

        if (null !== $indexConfig->getDynamicDateFormats()) {
            $mapping['dynamic_date_formats'] = $indexConfig->getDynamicDateFormats();
        }

        if (null !== $indexConfig->getDateDetection()) {
            $mapping['date_detection'] = $indexConfig->getDateDetection();
        }

        if (null !== $indexConfig->getNumericDetection()) {
            $mapping['numeric_detection'] = $indexConfig->getNumericDetection();
        }

        if ($indexConfig->getAnalyzer()) {
            $mapping['analyzer'] = $indexConfig->getAnalyzer();
        }

        if (null !== $indexConfig->getDynamic()) {
            $mapping['dynamic'] = $indexConfig->getDynamic();
        }

        if (isset($mapping['dynamic_templates']) and empty($mapping['dynamic_templates'])) {
            unset($mapping['dynamic_templates']);
        }

        $this->fixProperties($mapping['properties']);
        if (!$mapping['properties']) {
            unset($mapping['properties']);
        }

        if ($model) {
            $mapping['_meta']['model'] = $model;
        }

        if (empty($mapping)) {
            // Empty mapping, we want it encoded as a {} instead of a []
            $mapping = new \ArrayObject();
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
            unset($property['property_path']);

            if (!isset($property['type'])) {
                $property['type'] = 'text';
            }
            if (isset($property['fields'])) {
                $this->fixProperties($property['fields']);
            }
            if (isset($property['properties'])) {
                $this->fixProperties($property['properties']);
            }
        }
    }
}
