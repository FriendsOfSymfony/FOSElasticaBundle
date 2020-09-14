<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Configuration;

/**
 * Index configuration trait class.
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
     * The model of the index.
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
     * @var array
     */
    private $config;

    /**
     * @var array
     */
    private $mapping;

    public function getElasticSearchName(): string
    {
        return $this->elasticSearchName;
    }

    public function getModel(): ?string
    {
        return $this->model;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSettings(): array
    {
        return $this->settings;
    }

    public function getDateDetection(): ?bool
    {
        return $this->config['date_detection'] ?? null;
    }

    public function getDynamicDateFormats(): ?array
    {
        return $this->config['dynamic_date_formats'] ?? null;
    }

    public function getAnalyzer(): ?string
    {
        return $this->config['analyzer'] ?? null;
    }

    public function getMapping(): array
    {
        return $this->mapping;
    }

    public function getNumericDetection(): ?bool
    {
        return $this->config['numeric_detection'] ?? null;
    }

    public function getDynamic()
    {
        return $this->config['dynamic'] ?? null;
    }
}
