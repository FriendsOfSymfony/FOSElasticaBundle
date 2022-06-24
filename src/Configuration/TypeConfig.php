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
 * @phpstan-import-type TElasticConfig from IndexConfigInterface
 * @phpstan-import-type TMapping from IndexConfigInterface
 * @phpstan-import-type TDynamicDateFormats from IndexConfigInterface
 * @phpstan-import-type TDynamic from IndexConfigInterface
 */
class TypeConfig
{
    /**
     * @var array
     * @phpstan-var TElasticConfig
     */
    private $config;

    /**
     * @var array
     * @phpstan-var TMapping
     */
    private $mapping;

    /**
     * @var string
     */
    private $name;

    /**
     * @param TMapping $mapping
     * @phpstan-param TElasticConfig $config
     */
    public function __construct(string $name, array $mapping, array $config = [])
    {
        $this->config = $config;
        $this->mapping = $mapping;
        $this->name = $name;
    }

    public function getDateDetection(): ?bool
    {
        return $this->config['date_detection'] ?? null;
    }

    /**
     * @phpstan-return ?TDynamicDateFormats
     */
    public function getDynamicDateFormats(): ?array
    {
        return $this->config['dynamic_date_formats'] ?? null;
    }

    public function getAnalyzer(): ?string
    {
        return $this->config['analyzer'] ?? null;
    }

    /**
     * @phpstan-return TMapping
     */
    public function getMapping(): array
    {
        return $this->mapping;
    }

    public function getNumericDetection(): ?bool
    {
        return $this->config['numeric_detection'] ?? null;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @phpstan-return ?TDynamic
     */
    public function getDynamic()
    {
        return $this->config['dynamic'] ?? null;
    }
}
