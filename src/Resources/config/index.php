<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <https://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use FOS\ElasticaBundle\Elastica\Index;
use FOS\ElasticaBundle\Elastica\IndexTemplate;
use FOS\ElasticaBundle\Finder\TransformedFinder;
use FOS\ElasticaBundle\Index\AliasProcessor;
use FOS\ElasticaBundle\Index\IndexManager;
use FOS\ElasticaBundle\Index\IndexTemplateManager;
use FOS\ElasticaBundle\Index\Resetter;
use FOS\ElasticaBundle\Index\TemplateResetter;
use FOS\ElasticaBundle\Manager\RepositoryManager;
use FOS\ElasticaBundle\Provider\Indexable;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set('fos_elastica.repository_manager', RepositoryManager::class)
        ->args([abstract_arg('repository service locator')])
    ;

    $services->set('fos_elastica.alias_processor', AliasProcessor::class);

    $services->set('fos_elastica.indexable', Indexable::class)
        ->args([[]])
    ;

    $services->set('fos_elastica.index_prototype', Index::class)
        ->abstract()
        ->args([abstract_arg('index name')])
    ;

    $services->set('fos_elastica.index_template_prototype', IndexTemplate::class)
        ->abstract()
        ->args([abstract_arg('index template name')])
    ;

    $services->set('fos_elastica.index_manager', IndexManager::class)
        ->args([
            abstract_arg('indexes'),
            service('fos_elastica.index'),
        ])
    ;
    $services->alias(IndexManager::class, 'fos_elastica.index_manager');

    $services->set('fos_elastica.index_template_manager', IndexTemplateManager::class)
        ->args([abstract_arg('indexes templates')])
    ;
    $services->alias(IndexTemplateManager::class, 'fos_elastica.index_template_manager');

    $services->set('fos_elastica.resetter', Resetter::class)
        ->args([
            service('fos_elastica.config_manager'),
            service('fos_elastica.index_manager'),
            service('fos_elastica.alias_processor'),
            service('fos_elastica.mapping_builder'),
            service('event_dispatcher'),
        ])
    ;
    $services->alias(Resetter::class, 'fos_elastica.resetter');

    $services->set('fos_elastica.template_resetter', TemplateResetter::class)
        ->args([
            service('fos_elastica.config_manager.index_templates'),
            service('fos_elastica.mapping_builder'),
            service('fos_elastica.index_template_manager'),
        ])
    ;
    $services->alias(TemplateResetter::class, 'fos_elastica.template_resetter');

    $services->set('fos_elastica.finder', TransformedFinder::class)
        ->abstract()
        ->args([
            abstract_arg('searchable'),
            abstract_arg('transformer'),
        ])
    ;
};
