<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Provider;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * References persistence providers for each index and type.
 */
class PagerProviderRegistry implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /** @var array */
    private $providers = [];

    /**
     * @param array $providers
     */
    public function __construct(array $providers)
    {
        $this->providers = $providers;
    }

    /**
     * Gets all registered providers.
     *
     * Providers will be indexed by "index/type" strings in the returned array.
     *
     * @return PagerProviderInterface[]
     */
    public function getAllProviders()
    {
        $providers = [];

        foreach ($this->providers as $index => $indexProviders) {
            foreach ($indexProviders as $type => $providerId) {
                $providers[sprintf('%s/%s', $index, $type)] = $this->container->get($providerId);
            }
        }

        return $providers;
    }

    /**
     * Gets all providers for an index.
     *
     * Providers will be indexed by "type" strings in the returned array.
     *
     * @param string $index
     *
     * @return PagerProviderInterface[]|
     *
     * @throws \InvalidArgumentException if no providers were registered for the index
     */
    public function getIndexProviders($index)
    {
        if (!isset($this->providers[$index])) {
            throw new \InvalidArgumentException(sprintf('No providers were registered for index "%s".', $index));
        }

        $providers = [];
        foreach ($this->providers[$index] as $type => $providerId) {
            $providers[$type] = $this->getProvider($index, $type);
        }

        return $providers;
    }

    /**
     * Gets the provider for an index and type.
     *
     * @param string $index
     * @param string $type
     *
     * @return PagerProviderInterface
     *
     * @throws \InvalidArgumentException if no provider was registered for the index and type
     */
    public function getProvider($index, $type)
    {
        if (!isset($this->providers[$index][$type])) {
            throw new \InvalidArgumentException(sprintf('No provider was registered for index "%s" and type "%s".', $index, $type));
        }

        return $this->container->get($this->providers[$index][$type]);
    }
}
