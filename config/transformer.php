<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function(ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();

    $services->set('fos_elastica.model_to_elastica_transformer', 'FOS\ElasticaBundle\Transformer\ModelToElasticaAutoTransformer')
        ->abstract()
        ->args([
            [],
            service('event_dispatcher'),
        ])
        ->call('setPropertyAccessor', [service('fos_elastica.property_accessor')]);

    $services->set('fos_elastica.model_to_elastica_identifier_transformer', 'FOS\ElasticaBundle\Transformer\ModelToElasticaIdentifierTransformer')
        ->abstract()
        ->args([[]])
        ->call('setPropertyAccessor', [service('fos_elastica.property_accessor')]);
};
