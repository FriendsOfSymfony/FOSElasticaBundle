<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set('fos_elastica.serializer_callback_prototype')
        ->abstract()
        ->call('setSerializer', [service('fos_elastica.serializer')]);
};
