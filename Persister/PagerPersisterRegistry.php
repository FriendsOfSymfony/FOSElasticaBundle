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

final class PagerPersisterRegistry implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /** 
     * @var string[]
     */
    private $nameToServiceIdMap = [];

    /**
     * @param string[] $nameToServiceIdMap
     */
    public function __construct(array $nameToServiceIdMap)
    {
        $this->nameToServiceIdMap = $nameToServiceIdMap;
    }

    /**
     * @param string $name
     *
     * @throws \InvalidArgumentException if no pager persister was registered for the given name
     *
     * @return PagerPersisterInterface
     */
    public function getPagerPersister($name)
    {
        if (!isset($this->nameToServiceIdMap[$name])) {
            throw new \InvalidArgumentException(sprintf('No pager persister was registered for the give name "%s".', $name));
        }

        $serviceId = $this->nameToServiceIdMap[$name];

        $pagerPersister = $this->container->get($serviceId);

        if (!$pagerPersister instanceof PagerPersisterInterface) {
            throw new \LogicException(sprintf(
                'The pager provider service "%s" must implement "%s" interface but it is an instance of "%s" class.',
                $serviceId,
                PagerPersisterInterface::class,
                get_class($pagerPersister)
            ));
        }

        return $pagerPersister;
    }
}
