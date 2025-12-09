<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use FOS\ElasticaBundle\Event\PostIndexPopulateEvent;
use FOS\ElasticaBundle\EventListener\PopulateListener;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set('fos_elastica.populate_listener', PopulateListener::class)
        ->tag('kernel.event_listener', [
            'event' => PostIndexPopulateEvent::class,
            'method' => 'onPostIndexPopulate',
            'priority' => -9999,
        ])
        ->args([service('fos_elastica.resetter')]);
};
