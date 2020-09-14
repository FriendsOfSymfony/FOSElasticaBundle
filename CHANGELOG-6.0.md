CHANGELOG for 6.0.x
===================

This changelog references the relevant changes (bug and security fixes) done
in 6.0 versions.

### 6.0.0 (unreleased)

* Added Symfony 5 support.
* Added Elasticsearch 7 support.
* Dropped Symfony 3 support.
* Dropped Elasticsearch 5 and 6 support.
* Dropped PHP 7.1 support.
* Removed `FOS\ElasticaBundle\Exception\InvalidArgumentTypeException`
* [BC break] Signature of method `FOS\ElasticaBundle\HybridResult::getResult()` was changed.
* [BC break] Signature of method `FOS\ElasticaBundle\Finder\FinderInterface::find()` was changed.
* [BC break] Signature of method `FOS\ElasticaBundle\Persister\ObjectPersisterInterface::handlesObject()` was changed.
* [BC break] Signature of method `FOS\ElasticaBundle\Provider\PagerProviderInterface::provider()` was changed.
* [BC break] Signature of methods `getPager`, `getOptions` and `getObjectPersister` from interface `FOS\ElasticaBundle\Persister\Event\PersistEvent` were changed.
* [BC break] Signature of methods `getNbResults`, `getNbPages`, `getCurrentPage`, `setCurrentPage`, `getMaxPerPage` and `setMaxPerPage` from interface `FOS\ElasticaBundle\Provider\PagerInterface` were changed.
* [BC break] Signature of methods `findPaginated`, `createPaginatorAdapter` and `createRawPaginatorAdapter` from interface `FOS\ElasticaBundle\Finder\PaginatedFinderInterface` were changed.
* [BC break] Signature of methods `request`, `getIndex`, `getIndexTemplate` and `setStopwatch` from class `FOS\ElasticaBundle\Elastica\Client` were changed.
* [BC break] Signature of methods `logQuery`, `getNbQueries` and `getQueries` from class `FOS\ElasticaBundle\Logger\ElasticaLogger` were changed.
* [BC break] Removed `Elastica\Type`.
* [BC break] Removed `_parent`.
* [BC Break] Removed `FOS\ElasticaBundle\Persister\Event\Events` class, use class events instead.
* [BC break] Renamed `FOS\ElasticaBundle\Persister\Event\OnExceptionEvent::setIgnore()` to `FOS\ElasticaBundle\Persister\Event\OnExceptionEvent::setIgnored()`.
