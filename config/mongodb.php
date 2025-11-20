<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function(ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();

    $services->set('fos_elastica.pager_provider.prototype.mongodb', 'FOS\ElasticaBundle\Doctrine\MongoDBPagerProvider')
        ->abstract()
        ->args([
            service('doctrine_mongodb'),
            service('fos_elastica.doctrine.register_listeners'),
            '',
            [],
        ]);

    $services->set('fos_elastica.doctrine.register_listeners', 'FOS\ElasticaBundle\Doctrine\RegisterListenersService')
        ->args([service('event_dispatcher')]);

    $services->set('fos_elastica.listener.prototype.mongodb', 'FOS\ElasticaBundle\Doctrine\Listener')
        ->abstract()
        ->args([
            '',
            service('fos_elastica.indexable'),
            [],
            null,
        ]);

    $services->set('fos_elastica.elastica_to_model_transformer.prototype.mongodb', 'FOS\ElasticaBundle\Doctrine\MongoDB\ElasticaToModelTransformer')
        ->abstract()
        ->args([
            service('doctrine_mongodb'),
            '',
            [],
        ])
        ->call('setPropertyAccessor', [service('fos_elastica.property_accessor')]);

    $services->set('fos_elastica.manager.mongodb', 'FOS\ElasticaBundle\Doctrine\RepositoryManager')
        ->args([
            service('doctrine_mongodb'),
            service('fos_elastica.repository_manager'),
        ]);
};
