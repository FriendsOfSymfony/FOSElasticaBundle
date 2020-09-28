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

final class PagerPersisterRegistry
{
    /** @var ServiceLocator */
    private $persisters;

    public function __construct(ServiceLocator $persisters)
    {
        $this->persisters = $persisters;
    }

    /**
     * @throws \InvalidArgumentException if no pager persister was registered for the given name
     */
    public function getPagerPersister(string $name): PagerPersisterInterface
    {
        if (!$this->persisters->has($name)) {
            throw new \InvalidArgumentException(\sprintf('No pager persister was registered for the give name "%s".', $name));
        }

        return $this->persisters->get($name);
    }
}
