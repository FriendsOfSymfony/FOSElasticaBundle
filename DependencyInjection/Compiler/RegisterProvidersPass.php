<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @deprecated since 4.x and will be removed in 5.0
 */
class RegisterProvidersPass implements CompilerPassInterface
{
    /**
     * Mapping of class names to booleans indicating whether the class
     * implements ProviderInterface.
     *
     * @var array
     */
    private $implementations = [];

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('fos_elastica.provider_registry')) {
            return;
        }

        $defaultIndex = $container->getParameter('fos_elastica.default_index');
        $registry = $container->getDefinition('fos_elastica.provider_registry');
        $providers = $container->findTaggedServiceIds('fos_elastica.provider');

        $providersByPriority = [];
        foreach ($providers as $id => $attributes) {
            $priority = isset($attributes[0]['priority']) ? $attributes[0]['priority'] : 0;
            $providersByPriority[$priority][$id] = $attributes;
        }

        if (!empty($providersByPriority)) {
            krsort($providersByPriority);
            $providersByPriority = call_user_func_array('array_merge', $providersByPriority);
        }

        foreach ($providersByPriority as $providerId => $tags) {
            $index = $type = null;
            $class = $container->getDefinition($providerId)->getClass();

            if (!$class || !$this->isProviderImplementation($class)) {
                throw new \InvalidArgumentException(sprintf('Elastica provider "%s" with class "%s" must implement ProviderInterface.', $providerId, $class));
            }

            foreach ($tags as $tag) {
                if (!isset($tag['type'])) {
                    throw new \InvalidArgumentException(sprintf('Elastica provider "%s" must specify the "type" attribute.', $providerId));
                }

                $index = isset($tag['index']) ? $tag['index'] : $defaultIndex;
                $type = $tag['type'];
            }

            $registry->addMethodCall('addProvider', [$index, $type, $providerId]);
        }
    }

    /**
     * Returns whether the class implements ProviderInterface.
     *
     * @param string $class
     *
     * @return bool
     */
    private function isProviderImplementation($class)
    {
        if (!isset($this->implementations[$class])) {
            $refl = new \ReflectionClass($class);
            $this->implementations[$class] = $refl->implementsInterface('FOS\ElasticaBundle\Provider\ProviderInterface');
        }

        return $this->implementations[$class];
    }
}
