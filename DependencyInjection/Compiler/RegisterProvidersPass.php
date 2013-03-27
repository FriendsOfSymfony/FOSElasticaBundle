<?php

namespace FOS\ElasticaBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

class RegisterProvidersPass implements CompilerPassInterface
{
    /**
     * Mapping of class names to booleans indicating whether the class
     * implements ProviderInterface.
     *
     * @var array
     */
    private $implementations = array();

    /**
     * @see Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface::process()
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('fos_elastica.provider_registry')) {
            return;
        }

        // Infer the default index name from the service alias
        $defaultIndex = substr($container->getAlias('fos_elastica.index'), 19);

        $registry = $container->getDefinition('fos_elastica.provider_registry');
        $providers = $container->findTaggedServiceIds('fos_elastica.provider');

        foreach ($providers as $providerId => $tags) {
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

            $registry->addMethodCall('addProvider', array($index, $type, $providerId));
        }
    }

    /**
     * Returns whether the class implements ProviderInterface.
     *
     * @param string $class
     * @return boolean
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
