<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <https://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Provider;

use Symfony\Component\DependencyInjection\ServiceLocator;

/**
 * References persistence providers for each index.
 */
class PagerProviderRegistry
{
    private ServiceLocator $providers;

    public function __construct(ServiceLocator $providers)
    {
        $this->providers = $providers;
    }

    /**
     * Gets all registered providers.
     *
     * Providers will be indexed by "index" strings in the returned array.
     *
     * @return PagerProviderInterface[]
     */
    public function getProviders(): array
    {
        return \array_reduce(\array_keys($this->providers->getProvidedServices()), function ($carry, $index) {
            return $carry + [$index => $this->providers->get($index)];
        }, []);
    }

    /**
     * Gets the provider for an index.
     *
     * @throws \InvalidArgumentException if no provider was registered for the index and type
     */
    public function getProvider(string $index): PagerProviderInterface
    {
        if (!$this->providers->has($index)) {
            throw new \InvalidArgumentException(\sprintf('No provider was registered for index "%s".', $index));
        }

        return $this->providers->get($index);
    }
}
