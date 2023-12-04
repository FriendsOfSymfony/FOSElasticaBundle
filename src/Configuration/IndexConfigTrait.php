<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <https://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Configuration;

/**
 * Index configuration trait class.
 *
 * @author Dmitry Balabka <dmitry.balabka@intexsys.lv>
 *
 * @phpstan-import-type TMapping from IndexConfigInterface
 * @phpstan-import-type TSettings from IndexConfigInterface
 * @phpstan-import-type TConfig from IndexConfigInterface
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
     *
     * @phpstan-var TSettings
     *
     * @phpstan-ignore-next-line Ignored because of a bug in PHPStan (https://github.com/phpstan/phpstan/issues/5091)
     */
    private $settings;

    /**
     * @var array
     *
     * @phpstan-var TConfig
     *
     * @phpstan-ignore-next-line Ignored because of a bug in PHPStan (https://github.com/phpstan/phpstan/issues/5091)
     */
    private $config;

    /**
     * @var array
     *
     * @phpstan-var TMapping
     *
     * @phpstan-ignore-next-line Ignored because of a bug in PHPStan (https://github.com/phpstan/phpstan/issues/5091)
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
        // @phpstan-ignore-next-line Ignored because of a bug in PHPStan (https://github.com/phpstan/phpstan/issues/5091)
        return $this->settings;
    }

    public function getDateDetection(): ?bool
    {
        // @phpstan-ignore-next-line Ignored because of a bug in PHPStan (https://github.com/phpstan/phpstan/issues/5091)
        return $this->config['date_detection'] ?? null;
    }

    public function getDynamicDateFormats(): ?array
    {
        // @phpstan-ignore-next-line Ignored because of a bug in PHPStan (https://github.com/phpstan/phpstan/issues/5091)
        return $this->config['dynamic_date_formats'] ?? null;
    }

    public function getAnalyzer(): ?string
    {
        // @phpstan-ignore-next-line Ignored because of a bug in PHPStan (https://github.com/phpstan/phpstan/issues/5091)
        return $this->config['analyzer'] ?? null;
    }

    public function getMapping(): array
    {
        // @phpstan-ignore-next-line Ignored because of a bug in PHPStan (https://github.com/phpstan/phpstan/issues/5091)
        return $this->mapping;
    }

    public function getNumericDetection(): ?bool
    {
        // @phpstan-ignore-next-line Ignored because of a bug in PHPStan (https://github.com/phpstan/phpstan/issues/5091)
        return $this->config['numeric_detection'] ?? null;
    }

    public function getDynamic()
    {
        // @phpstan-ignore-next-line Ignored because of a bug in PHPStan (https://github.com/phpstan/phpstan/issues/5091)
        return $this->config['dynamic'] ?? null;
    }
}
