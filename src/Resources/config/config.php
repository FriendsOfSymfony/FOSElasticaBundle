<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use FOS\ElasticaBundle\Configuration\ConfigManager;
use FOS\ElasticaBundle\DataCollector\ElasticaDataCollector;
use FOS\ElasticaBundle\Elastica\Client;
use FOS\ElasticaBundle\Elastica\NodePool\RoundRobinNoResurrect;
use FOS\ElasticaBundle\Elastica\NodePool\RoundRobinResurrect;
use FOS\ElasticaBundle\Index\MappingBuilder;
use FOS\ElasticaBundle\Logger\ElasticaLogger;
use FOS\ElasticaBundle\Subscriber\PaginateElasticaQuerySubscriber;
use Symfony\Component\PropertyAccess\PropertyAccessor;

return static function (ContainerConfigurator $container): void {
    $container->parameters()
        ->set('fos_elastica.property_accessor.magicCall', 0)
        ->set('fos_elastica.property_accessor.throwExceptionOnInvalidIndex', 0);

    $services = $container->services();

    $services->set('fos_elastica.client_prototype', Client::class)
        ->abstract()
        ->arg('$config', abstract_arg('configuration for Ruflin Client'))
        ->arg('$forbiddenCodes', abstract_arg('list of forbidden codes for Client'))
        ->arg('$logger', abstract_arg('logger for Ruflin Client'))
        ->call('setStopwatch', [service('debug.stopwatch')->nullOnInvalid()])
        ->call('setEventDispatcher', [service('event_dispatcher')->nullOnInvalid()]);

    $services->set(RoundRobinResurrect::class)
        ->factory([null, 'create']);

    $services->set(RoundRobinNoResurrect::class)
        ->factory([null, 'create']);

    $services->set('fos_elastica.config_manager', ConfigManager::class)
        ->args([[]]);
    $services->alias(ConfigManager::class, 'fos_elastica.config_manager');

    $services->set('fos_elastica.config_manager.index_templates', ConfigManager::class)
        ->args([[]]);

    $services->set('fos_elastica.data_collector', ElasticaDataCollector::class)
        ->tag('data_collector', ['template' => '@FOSElastica/Collector/elastica.html.twig', 'id' => 'elastica'])
        ->args([service('fos_elastica.logger')]);

    $services->set('fos_elastica.paginator.subscriber', PaginateElasticaQuerySubscriber::class)
        ->tag('kernel.event_subscriber')
        ->args([service('request_stack')]);

    $services->set('fos_elastica.logger', ElasticaLogger::class)
        ->args([service('logger')->nullOnInvalid(), '%kernel.debug%'])
        ->tag('monolog.logger', ['channel' => 'elastica']);

    $services->set('fos_elastica.mapping_builder', MappingBuilder::class)
        ->args([service('event_dispatcher')]);

    $services->set('fos_elastica.property_accessor', PropertyAccessor::class)
        ->args(['%fos_elastica.property_accessor.magicCall%', '%fos_elastica.property_accessor.throwExceptionOnInvalidIndex%']);
};
