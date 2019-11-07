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
    public function getElasticSearchName(): string;

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @return array
     */
    public function getSettings(): array;

    /**
     * @throws \InvalidArgumentException
     */
    public function getType(string $typeName): TypeConfig;

    /**
     * @return \FOS\ElasticaBundle\Configuration\TypeConfig[]
     */
    public function getTypes();
}
