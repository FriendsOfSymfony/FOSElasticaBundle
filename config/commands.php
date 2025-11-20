<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function(ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();

    $services->set('fos_elastica.command.create', 'FOS\ElasticaBundle\Command\CreateCommand')
        ->args([
            service('fos_elastica.index_manager'),
            service('fos_elastica.mapping_builder'),
            service('fos_elastica.config_manager'),
            service('fos_elastica.alias_processor'),
        ])
        ->tag('console.command', ['command' => 'fos:elastica:create']);

    $services->set('fos_elastica.command.delete', 'FOS\ElasticaBundle\Command\DeleteCommand')
        ->args([service('fos_elastica.index_manager')])
        ->tag('console.command', ['command' => 'fos:elastica:delete']);

    $services->set('fos_elastica.command.populate', 'FOS\ElasticaBundle\Command\PopulateCommand')
        ->args([
            service('event_dispatcher'),
            service('fos_elastica.index_manager'),
            service('fos_elastica.pager_provider_registry'),
            service('fos_elastica.pager_persister_registry'),
            service('fos_elastica.resetter'),
        ])
        ->tag('console.command', ['command' => 'fos:elastica:populate']);

    $services->set('fos_elastica.command.reset', 'FOS\ElasticaBundle\Command\ResetCommand')
        ->args([
            service('fos_elastica.index_manager'),
            service('fos_elastica.resetter'),
        ])
        ->tag('console.command', ['command' => 'fos:elastica:reset']);

    $services->set('fos_elastica.command.templates_reset', 'FOS\ElasticaBundle\Command\ResetTemplatesCommand')
        ->args([service('fos_elastica.template_resetter')])
        ->tag('console.command', ['command' => 'fos:elastica:reset-templates']);

    $services->set('fos_elastica.command.search', 'FOS\ElasticaBundle\Command\SearchCommand')
        ->args([service('fos_elastica.index_manager')])
        ->tag('console.command', ['command' => 'fos:elastica:search']);
};
