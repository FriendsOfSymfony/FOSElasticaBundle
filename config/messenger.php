<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function(ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();

    $services->set('fos_elastica.async_pager_persister', 'FOS\ElasticaBundle\Persister\AsyncPagerPersister')
        ->args([
            service('fos_elastica.pager_persister_registry'),
            service('fos_elastica.pager_provider_registry'),
            service('fos_elastica.messenger.bus'),
        ])
        ->tag('fos_elastica.pager_persister', ['persisterName' => 'async']);
};
