<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function(ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();

    $services->set('fos_elastica.config_source.container', 'FOS\ElasticaBundle\Configuration\Source\ContainerSource')
        ->args([[]])
        ->tag('fos_elastica.config_source');

    $services->set('fos_elastica.config_source.template_container', 'FOS\ElasticaBundle\Configuration\Source\TemplateContainerSource')
        ->args([[]])
        ->tag('fos_elastica.config_source', ['source' => 'index_template']);
};
