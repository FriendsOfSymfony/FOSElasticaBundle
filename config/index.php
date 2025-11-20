<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function(ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();

    $services->set('fos_elastica.repository_manager', 'FOS\ElasticaBundle\Manager\RepositoryManager');

    $services->set('fos_elastica.alias_processor', 'FOS\ElasticaBundle\Index\AliasProcessor');

    $services->set('fos_elastica.indexable', 'FOS\ElasticaBundle\Provider\Indexable')
        ->args([[]]);

    $services->set('fos_elastica.index_prototype', 'FOS\ElasticaBundle\Elastica\Index')
        ->abstract()
        ->args(['']);

    $services->set('fos_elastica.index_template_prototype', 'FOS\ElasticaBundle\Elastica\IndexTemplate')
        ->abstract()
        ->args(['']);

    $services->set('fos_elastica.index_manager', 'FOS\ElasticaBundle\Index\IndexManager')
        ->args([
            '',
            service('fos_elastica.index'),
        ]);

    $services->alias('FOS\ElasticaBundle\Index\IndexManager', 'fos_elastica.index_manager');

    $services->set('fos_elastica.index_template_manager', 'FOS\ElasticaBundle\Index\IndexTemplateManager')
        ->args(['']);

    $services->alias('FOS\ElasticaBundle\Index\IndexTemplateManager', 'fos_elastica.index_template_manager');

    $services->set('fos_elastica.resetter', 'FOS\ElasticaBundle\Index\Resetter')
        ->args([
            service('fos_elastica.config_manager'),
            service('fos_elastica.index_manager'),
            service('fos_elastica.alias_processor'),
            service('fos_elastica.mapping_builder'),
            service('event_dispatcher'),
        ]);

    $services->alias('FOS\ElasticaBundle\Index\Resetter', 'fos_elastica.resetter');

    $services->set('fos_elastica.template_resetter', 'FOS\ElasticaBundle\Index\TemplateResetter')
        ->args([
            service('fos_elastica.config_manager.index_templates'),
            service('fos_elastica.mapping_builder'),
            service('fos_elastica.index_template_manager'),
        ]);

    $services->alias('FOS\ElasticaBundle\Index\TemplateResetter', 'fos_elastica.template_resetter');

    $services->set('fos_elastica.finder', 'FOS\ElasticaBundle\Finder\TransformedFinder')
        ->abstract()
        ->args([
            '',
            '',
        ]);
};
