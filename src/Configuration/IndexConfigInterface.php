<?php

namespace FOS\ElasticaBundle\Configuration;

/**
 * Interface Index config interface
 *
 * @author Dmitry Balabka <dmitry.balabka@intexsys.lv>
 */
interface IndexConfigInterface
{
    /**
     * @return string
     */
    public function getElasticSearchName();

    /**
     * @return string
     */
    public function getName();

    /**
     * @return array
     */
    public function getSettings();

    /**
     * @param string $typeName
     *
     * @return TypeConfig
     *
     * @throws \InvalidArgumentException
     */
    public function getType($typeName);

    /**
     * @return \FOS\ElasticaBundle\Configuration\TypeConfig[]
     */
    public function getTypes();
}
