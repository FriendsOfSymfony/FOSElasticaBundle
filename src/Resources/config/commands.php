<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use FOS\ElasticaBundle\Command\CreateCommand;
use FOS\ElasticaBundle\Command\DeleteCommand;
use FOS\ElasticaBundle\Command\PopulateCommand;
use FOS\ElasticaBundle\Command\ResetCommand;
use FOS\ElasticaBundle\Command\ResetTemplatesCommand;
use FOS\ElasticaBundle\Command\SearchCommand;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set('fos_elastica.command.create', CreateCommand::class)
        ->tag('console.command', ['command' => 'fos:elastica:create'])
        ->args([
            service('fos_elastica.index_manager'),
            service('fos_elastica.mapping_builder'),
            service('fos_elastica.config_manager'),
            service('fos_elastica.alias_processor'),
        ]);

    $services->set('fos_elastica.command.delete', DeleteCommand::class)
        ->tag('console.command', ['command' => 'fos:elastica:delete'])
        ->args([service('fos_elastica.index_manager')]);

    $services->set('fos_elastica.command.populate', PopulateCommand::class)
        ->tag('console.command', ['command' => 'fos:elastica:populate'])
        ->args([
            service('event_dispatcher'),
            service('fos_elastica.index_manager'),
            service('fos_elastica.pager_provider_registry'),
            service('fos_elastica.pager_persister_registry'),
            service('fos_elastica.resetter'),
        ]);

    $services->set('fos_elastica.command.reset', ResetCommand::class)
        ->tag('console.command', ['command' => 'fos:elastica:reset'])
        ->args([
            service('fos_elastica.index_manager'),
            service('fos_elastica.resetter'),
        ]);

    $services->set('fos_elastica.command.templates_reset', ResetTemplatesCommand::class)
        ->tag('console.command', ['command' => 'fos:elastica:reset-templates'])
        ->args([service('fos_elastica.template_resetter')]);

    $services->set('fos_elastica.command.search', SearchCommand::class)
        ->tag('console.command', ['command' => 'fos:elastica:search'])
        ->args([service('fos_elastica.index_manager')]);
};
