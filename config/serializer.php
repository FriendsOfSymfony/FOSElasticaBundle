<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function(ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();

    $services->set('fos_elastica.serializer_callback_prototype')
        ->abstract()
        ->call('setSerializer', [service('fos_elastica.serializer')]);
};
