<?php
/*
 * This file is part of the OpCart software.
 *
 * (c) 2015, OpticsPlanet, Inc
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Configuration;

/**
 * Index configuration trait class
 *
 * @author Dmitry Balabka <dmitry.balabka@intexsys.lv>
 */
trait IndexConfigTrait
{
    /**
     * The name of the index for ElasticSearch.
     *
     * @var string
     */
    private $elasticSearchName;

    /**
     * The model of the index
     *
     * @var string|null
     */
    private $model;

    /**
     * The internal name of the index. May not be the same as the name used in ElasticSearch,
     * especially if aliases are enabled.
     *
     * @var string
     */
    private $name;

    /**
     * An array of settings sent to ElasticSearch when creating the index.
     *
     * @var array
     */
    private $settings;

    /**
     * All types that belong to this index.
     *
     * @var TypeConfig[]
     */
    private $types;

    /**
     * @return string
     */
    public function getElasticSearchName(): string
    {
        return $this->elasticSearchName;
    }

    public function getModel(): ?string
    {
        return $this->model;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return array
     */
    public function getSettings(): array
    {
        return $this->settings;
    }

    /**s
     * @throws \InvalidArgumentException
     */
    public function getType(string $typeName): TypeConfig
    {
        if (!array_key_exists($typeName, $this->types)) {
            throw new \InvalidArgumentException(sprintf('Type "%s" does not exist on index "%s"', $typeName, $this->name));
        }

        return $this->types[$typeName];
    }

    /**
     * @return \FOS\ElasticaBundle\Configuration\TypeConfig[]
     */
    public function getTypes()
    {
        return $this->types;
    }
}
