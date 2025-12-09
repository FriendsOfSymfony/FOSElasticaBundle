<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use FOS\ElasticaBundle\Configuration\Source\ContainerSource;
use FOS\ElasticaBundle\Configuration\Source\TemplateContainerSource;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set('fos_elastica.config_source.container', ContainerSource::class)
        ->args([[]])
        ->tag('fos_elastica.config_source');

    $services->set('fos_elastica.config_source.template_container', TemplateContainerSource::class)
        ->args([[]])
        ->tag('fos_elastica.config_source', ['source' => 'index_template']);
};
