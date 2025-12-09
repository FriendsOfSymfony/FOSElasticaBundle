<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use FOS\ElasticaBundle\Doctrine\Listener;
use FOS\ElasticaBundle\Doctrine\ORM\ElasticaToModelTransformer;
use FOS\ElasticaBundle\Doctrine\ORMPagerProvider;
use FOS\ElasticaBundle\Doctrine\RegisterListenersService;
use FOS\ElasticaBundle\Doctrine\RepositoryManager;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set('fos_elastica.pager_provider.prototype.orm', ORMPagerProvider::class)
        ->abstract()
        ->args([
            service('doctrine'),
            service('fos_elastica.doctrine.register_listeners'),
            null,
            [],
        ]);

    $services->set('fos_elastica.doctrine.register_listeners', RegisterListenersService::class)
        ->args([service('event_dispatcher')]);

    $services->set('fos_elastica.listener.prototype.orm', Listener::class)
        ->abstract()
        ->args([null, service('fos_elastica.indexable'), [], 'null']);

    $services->set('fos_elastica.elastica_to_model_transformer.prototype.orm', ElasticaToModelTransformer::class)
        ->abstract()
        ->args([service('doctrine'), null, []])
        ->call('setPropertyAccessor', [service('fos_elastica.property_accessor')]);

    $services->set('fos_elastica.manager.orm', RepositoryManager::class)
        ->args([service('doctrine'), service('fos_elastica.repository_manager')]);
};
