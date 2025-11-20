<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function(ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();

    $services->set('fos_elastica.pager_provider_registry', 'FOS\ElasticaBundle\Provider\PagerProviderRegistry')
        ->args([tagged_locator('fos_elastica.pager_provider', indexAttribute: 'index')]);
};
