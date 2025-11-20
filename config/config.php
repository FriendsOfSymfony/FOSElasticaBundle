<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function(ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();
    $parameters->set('fos_elastica.property_accessor.magicCall', 0);
    $parameters->set('fos_elastica.property_accessor.throwExceptionOnInvalidIndex', 0);

    $services->set('fos_elastica.client_prototype', 'FOS\ElasticaBundle\Elastica\Client')
        ->abstract()
        ->args([
            '$config' => abstract_arg('configuration for Ruflin Client'),
            '$forbiddenCodes' => abstract_arg('list of forbidden codes for Client'),
            '$logger' => abstract_arg('logger for Ruflin Client'),
        ])
        ->call('setStopwatch', [service('debug.stopwatch')->nullOnInvalid()])
        ->call('setEventDispatcher', [service('event_dispatcher')->nullOnInvalid()]);

    $services->set('FOS\ElasticaBundle\Elastica\NodePool\RoundRobinResurrect')
        ->factory([null, 'create']);

    $services->set('FOS\ElasticaBundle\Elastica\NodePool\RoundRobinNoResurrect')
        ->factory([null, 'create']);

    $services->set('fos_elastica.config_manager', 'FOS\ElasticaBundle\Configuration\ConfigManager')
        ->args([[]]);

    $services->alias('FOS\ElasticaBundle\Configuration\ConfigManager', 'fos_elastica.config_manager');

    $services->set('fos_elastica.config_manager.index_templates', 'FOS\ElasticaBundle\Configuration\ConfigManager')
        ->args([[]]);

    $services->set('fos_elastica.data_collector', 'FOS\ElasticaBundle\DataCollector\ElasticaDataCollector')
        ->args([service('fos_elastica.logger')])
        ->tag('data_collector', ['template' => '@FOSElastica/Collector/elastica.html.twig', 'id' => 'elastica']);

    $services->set('fos_elastica.paginator.subscriber', 'FOS\ElasticaBundle\Subscriber\PaginateElasticaQuerySubscriber')
        ->args([service('request_stack')])
        ->tag('kernel.event_subscriber');

    $services->set('fos_elastica.logger', 'FOS\ElasticaBundle\Logger\ElasticaLogger')
        ->args([
            service('logger')->nullOnInvalid(),
            '%kernel.debug%',
        ])
        ->tag('monolog.logger', ['channel' => 'elastica']);

    $services->set('fos_elastica.mapping_builder', 'FOS\ElasticaBundle\Index\MappingBuilder')
        ->args([service('event_dispatcher')]);

    $services->set('fos_elastica.property_accessor', 'Symfony\Component\PropertyAccess\PropertyAccessor')
        ->args([
            '%fos_elastica.property_accessor.magicCall%',
            '%fos_elastica.property_accessor.throwExceptionOnInvalidIndex%',
        ]);
};
