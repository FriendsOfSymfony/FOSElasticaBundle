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

use FOS\ElasticaBundle\Provider\PagerProviderInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class RegisterPagerProvidersPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('fos_elastica.pager_provider_registry')) {
            return;
        }

        $defaultIndex = $container->getParameter('fos_elastica.default_index');
        $registry = $container->getDefinition('fos_elastica.pager_provider_registry');

        $registeredProviders = [];
        foreach ($container->findTaggedServiceIds('fos_elastica.pager_provider') as $id => $attributes) {
            foreach ($attributes as $attribute) {
                if (!isset($attribute['type'])) {
                    throw new \InvalidArgumentException(sprintf('Elastica provider "%s" must specify the "type" attribute.', $id));
                }

                $index = isset($attribute['index']) ? $attribute['index'] : $defaultIndex;
                $type = $attribute['type'];

                if (isset($registeredProviders[$index][$type])) {
                    throw new \InvalidArgumentException(sprintf(
                        'Cannot register provider "%s". The provider "%s" has been registered for same index "%s" and type "%s"',
                        $registeredProviders[$index][$type],
                        $index,
                        $type
                    ));
                }

                $providerDef = $container->getDefinition($id);
                if (!$providerDef->getFactory()) {
                    // You are on your own if you use a factory to create a provider.
                    // It would fail in runtime if the factory does not return a proper provider.
                    $this->assertClassImplementsPagerProviderInterface($id, $providerDef->getClass());
                }

                $registeredProviders[$index][$type] = $id;
            }
        }

        $registry->replaceArgument(0, $registeredProviders);
    }

    /**
     * @param $providerId
     * @param $providerClass
     *
     * @throws \InvalidArgumentException if provider service does not implement PagerProviderInterface
     */
    private function assertClassImplementsPagerProviderInterface($providerId, $providerClass)
    {
        $rc = new \ReflectionClass($providerClass);

        if (!$rc->implementsInterface(PagerProviderInterface::class)) {
            throw new \InvalidArgumentException(sprintf(
                'Elastica provider "%s" with class "%s" must implement ProviderInterface.',
                $providerId,
                $providerClass
            ));
        }
    }
}
