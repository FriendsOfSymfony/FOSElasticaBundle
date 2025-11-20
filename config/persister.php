<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function(ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();

    $services->set('fos_elastica.in_place_pager_persister', 'FOS\ElasticaBundle\Persister\InPlacePagerPersister')
        ->args([
            service('fos_elastica.persister_registry'),
            service('event_dispatcher'),
        ])
        ->tag('fos_elastica.pager_persister', ['persisterName' => 'in_place']);

    $services->set('fos_elastica.pager_persister_registry', 'FOS\ElasticaBundle\Persister\PagerPersisterRegistry')
        ->args([tagged_locator('fos_elastica.pager_persister', indexAttribute: 'persisterName')]);

    $services->set('fos_elastica.persister_registry', 'FOS\ElasticaBundle\Persister\PersisterRegistry')
        ->args([tagged_locator('fos_elastica.persister', indexAttribute: 'index')]);

    $services->set('fos_elastica.filter_objects_listener', 'FOS\ElasticaBundle\Persister\Listener\FilterObjectsListener')
        ->args([service('fos_elastica.indexable')])
        ->tag('kernel.event_subscriber');

    $services->set('fos_elastica.object_persister', 'FOS\ElasticaBundle\Persister\ObjectPersister')
        ->abstract()
        ->args([
            '',
            '',
            '',
            '',
            '',
        ]);

    $services->set('fos_elastica.object_serializer_persister', 'FOS\ElasticaBundle\Persister\ObjectSerializerPersister')
        ->abstract()
        ->args([
            '',
            '',
            '',
            '',
            '',
        ]);
};
