<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <https://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Persister;

use Symfony\Component\DependencyInjection\ServiceLocator;

class PersisterRegistry
{
    private ServiceLocator $persisters;

    public function __construct(ServiceLocator $persisters)
    {
        $this->persisters = $persisters;
    }

    /**
     * Gets the persister for an index.
     *
     * @throws \InvalidArgumentException if no persister was registered for the index
     */
    public function getPersister(string $index): ObjectPersisterInterface
    {
        if (!$this->persisters->has($index)) {
            throw new \InvalidArgumentException(\sprintf('No persister was registered for index "%s".', $index));
        }

        return $this->persisters->get($index);
    }
}
