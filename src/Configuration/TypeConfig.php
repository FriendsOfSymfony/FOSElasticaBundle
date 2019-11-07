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

class TypeConfig
{
    /**
     * @var array
     */
    private $config;

    /**
     * @var array
     */
    private $mapping;

    /**
     * @var string
     */
    private $name;

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

    public function getModel(): ?string
    {
        return $this->config['persistence']['model'] ?? null;
    }

    public function getNumericDetection(): ?bool
    {
        return $this->config['numeric_detection'] ?? null;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /*
     * @return string|bool|null
     */
    public function getDynamic()
    {
        return $this->config['dynamic'] ?? null;
    }
}
