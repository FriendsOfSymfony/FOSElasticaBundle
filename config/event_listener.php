<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function(ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();

    $services->set('fos_elastica.populate_listener', 'FOS\ElasticaBundle\EventListener\PopulateListener')
        ->args([service('fos_elastica.resetter')])
        ->tag('kernel.event_listener', ['event' => 'FOS\ElasticaBundle\Event\PostIndexPopulateEvent', 'method' => 'onPostIndexPopulate', 'priority' => -9999]);
};
