CHANGELOG for 6.0.x
===================

This changelog references the relevant changes (bug and security fixes) done
in 6.0 versions.

### 6.0.0-BETA4 (2020-xx-xx)

* Added `auth_type` client parameter [Elastica#1790](https://github.com/ruflin/Elastica/pull/1790).
* Added a post mapping builder event.
* Fixed deprecations for `Elastica\Index::create()` [Elastica#1828](https://github.com/ruflin/Elastica/pull/1828).
* **[BC break]** Marked all `fos_elastica.finder.*` services as private.
* **[BC break]** Marked the `fos_elastica.client` alias as private.
* **[BC break]** Marked the `fos_elastica.client_prototype` service as private.
* **[BC break]** Marked the `fos_elastica.index_template_prototype` service as private.

### 6.0.0-BETA3 (2020-09-28)

* Fixed `symfony/messenger` integration.
* **[BC break]** Marked the `fos_elastica.index` alias as private.

### 6.0.0-BETA2 (2020-09-24)

* Added support for queued messages via Symfony Messenger.
* Fixed several Doctrine Persistence deprecations.
* Fixed missing options in `findHybridPaginated`.
* Fixed wrong class alias for `fos_elastica.template_resetter` service.
* **[BC break]** Changed signature of method `FOS\ElasticaBundle\Persister\PagerPersisterRegistry::getPagerPersister()`.
* **[BC break]** Marked all services tagged with `fos_elastica.pager_provider` private (`fos_elastica.pager_provider.prototype.mongodb`, `fos_elastica.pager_provider.prototype.orm` and `fos_elastica.pager_provider.prototype.phpcr` abstract services).
* **[BC break]** Marked the `fos_elastica.config_manager` service as private.
* **[BC break]** Marked the `fos_elastica.config_manager.index_templates` service as private.
* **[BC break]** Marked the `fos_elastica.filter_objects_listener` service as private.
* **[BC break]** Marked the `fos_elastica.finder` abstract service and all its children as private.
* **[BC break]** Marked the `fos_elastica.in_place_pager_persister` service as private.
* **[BC break]** Marked the `fos_elastica.index_template_manager` service as private.
* **[BC break]** Marked the `fos_elastica.object_persister` service as private.
* **[BC break]** Marked the `fos_elastica.object_serializer_persister` service as private.
* **[BC break]** Marked the `fos_elastica.pager_persister_registry` service as private.
* **[BC break]** Marked the `fos_elastica.persister_registry` service as private.
* **[BC break]** Marked the `fos_elastica.resetter` service as private.
* **[BC break]** Marked the `fos_elastica.template_resetter` service as private.
* **[BC break]** The configuration option `debug_logging` must be a boolean instead of a scalar value.

### 6.0.0-BETA1 (2020-09-15)

* Added Symfony 5 support.
* Added Elasticsearch 7 support.
* Dropped Symfony 3 support.
* Dropped Elasticsearch 5 and 6 support.
* Dropped PHP 7.1 support.
* Removed `FOS\ElasticaBundle\Exception\InvalidArgumentTypeException`.
* **[BC break]** Changed signature of method `FOS\ElasticaBundle\Finder\FinderInterface::find()`.
* **[BC break]** Changed signature of method `FOS\ElasticaBundle\HybridResult::getResult()`.
* **[BC break]** Changed signature of method `FOS\ElasticaBundle\Index\AliasProcessor::switchIndexAlias()`.
* **[BC break]** Changed signature of method `FOS\ElasticaBundle\Persister\ObjectPersisterInterface::handlesObject()`.
* **[BC break]** Changed signature of method `FOS\ElasticaBundle\Provider\PagerProviderInterface::provide()`.
* **[BC break]** Changed signature of methods `getPager`, `getOptions` and `getObjectPersister` from interface `FOS\ElasticaBundle\Persister\Event\PersistEvent`.
* **[BC break]** Changed signature of methods `getNbResults`, `getNbPages`, `getCurrentPage`, `setCurrentPage`, `getMaxPerPage` and `setMaxPerPage` from interface `FOS\ElasticaBundle\Provider\PagerInterface`.
* **[BC break]** Changed signature of methods `findPaginated`, `createPaginatorAdapter` and `createRawPaginatorAdapter` from interface `FOS\ElasticaBundle\Finder\PaginatedFinderInterface`.
* **[BC break]** Changed signature of methods `request`, `getIndex`, `getIndexTemplate` and `setStopwatch` from class `FOS\ElasticaBundle\Elastica\Client`.
* **[BC break]** Changed signature of methods `logQuery`, `getNbQueries` and `getQueries` from class `FOS\ElasticaBundle\Logger\ElasticaLogger`.
* **[BC break]** Changed signature of methods `addIndex`, `getRepository` and `getRepositoryName` from class `FOS\ElasticaBundle\Manager\RepositoryManager`.
* **[BC break]** Changed signature of methods `find`, `findHybrid`, `findPaginated` and `createPaginatorAdapter` from class `FOS\ElasticaBundle\Repository`.
* **[BC break]** Removed `Elastica\Type`.
* **[BC break]** Removed `_parent`.
* **[BC break]** Removed `FOS\ElasticaBundle\Event\IndexPopulateEvent` constants for event names, use event classes instead:
    - `PRE_INDEX_POPULATE` => `FOS\ElasticaBundle\Event\PreIndexPopulateEvent`
    - `POST_INDEX_POPULATE` => `FOS\ElasticaBundle\Event\PostIndexPopulateEvent`
* **[BC break]** Removed `FOS\ElasticaBundle\Event\IndexResetEvent` constants for event names, use event classes instead:
    - `PRE_INDEX_RESET` => `FOS\ElasticaBundle\Event\PreIndexResetEvent`
    - `POST_INDEX_RESET` => `FOS\ElasticaBundle\Event\PostIndexResetEvent`
* **[BC break]** Removed `FOS\ElasticaBundle\Event\TransformEvent` constants for event names, use event classes instead:
    - `PRE_TRANSFORM` => `FOS\ElasticaBundle\Event\PreTransformEvent`
    - `POST_TRANSFORM` => `FOS\ElasticaBundle\Event\PostTransformEvent`
* **[BC break]** Removed `FOS\ElasticaBundle\Persister\Event\Events` class, use event classes instead:
    - `ON_EXCEPTION` => `FOS\ElasticaBundle\Persister\Event\OnExceptionEvent`
    - `POST_ASYNC_INSERT_OBJECTS` => `FOS\ElasticaBundle\Persister\Event\PostAsyncInsertObjectsEvent`
    - `PRE_INSERT_OBJECTS` => `FOS\ElasticaBundle\Persister\Event\PreInsertObjectsEvent`
    - `POST_INSERT_OBJECTS` => `FOS\ElasticaBundle\Persister\Event\PostInsertObjectsEvent`
    - `PRE_PERSIST` => `FOS\ElasticaBundle\Persister\Event\PrePersistEvent`
    - `POST_PERSIST` => `FOS\ElasticaBundle\Persister\Event\PostPersistEvent`
    - `PRE_FETCH_OBJECTS` => `FOS\ElasticaBundle\Persister\Event\PreFetchObjectsEvent`
* **[BC break]** Renamed `FOS\ElasticaBundle\Persister\Event\OnExceptionEvent::setIgnore()` to `FOS\ElasticaBundle\Persister\Event\OnExceptionEvent::setIgnored()`.
* **[BC break]** Marked all `fos_elastica.manager` services as private.
* **[BC break]** Marked the `fos_elastica.repository_manager` service as private.
* **[BC break]** Marked the `fos_elastica.pager_provider_registry` service as private.
* **[BC break]** Marked the `fos_elastica.index_manager` service as private.
* **[BC break]** Marked the `fos_elastica.paginator.subscriber` service as private.
