<?php

namespace FOS\ElasticaBundle\Configuration;

/**
 * Interface Index config interface
 *
 * @author Dmitry Balabka <dmitry.balabka@intexsys.lv>
 */
interface IndexConfigInterface
{
    public function getElasticSearchName(): string;

    public function getModel(): ?string;

    public function getName(): string;

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
