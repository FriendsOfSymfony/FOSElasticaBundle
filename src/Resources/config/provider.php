<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use FOS\ElasticaBundle\Provider\PagerProviderRegistry;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set('fos_elastica.pager_provider_registry', PagerProviderRegistry::class)
        ->args([tagged_locator('fos_elastica.pager_provider', 'index')]);
};
