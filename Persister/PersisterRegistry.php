<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Persister;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class PersisterRegistry implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /** @var array */
    private $persisters = [];

    /**
     * @param string $index
     * @param string $type
     * @param string $persisterId
     */
    public function addPersister($index, $type, $persisterId)
    {
        if (!isset($this->persisters[$index])) {
            $this->persisters[$index] = [];
        }

        $this->persisters[$index][$type] = $persisterId;
    }

    /**
     * @return ObjectPersisterInterface
     */
    public function getPersister($index, $type)
    {
        if (!isset($this->persisters[$index][$type])) {
            throw new \InvalidArgumentException(sprintf('No persister was registered for index "%s" and type "%s".', $index, $type));
        }

        return $this->container->get($this->persisters[$index][$type]);
    }
}