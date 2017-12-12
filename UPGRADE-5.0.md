UPGRADE FROM 4.0 to 5.0
=======================

### Elastica library changes
Elastica has been updated to version 5.1. Please consider [their changelog for an upgrade guide](https://github.com/ruflin/Elastica/blob/master/CHANGELOG.md#510).

### API Changes
  * Removed `sortIgnoreUnmapped` support for Paginator. `ignore_unmapped` is not supported in ES 5.0 anymore.
  * Removed `ttl` and `timestamp` support in configuration. These attributes are not supported in ES 5.0 anymore.
  
### Provider Changes
The provider's related logic has been reworked completely (more in #1240 PR). 
Legacy providers did a lot so their responsibility was split apart into several objects.
A new `PagerProvider` is responsible for fetching object from database. 
It should return an instance of  `PagerInterface`. The pager provides a page-oriented access to objects.
There is a `InPlacePagerPersister` object that is responsible for iterating over a pager and persisting objects to the index.
It also dispatches different types of events, it helps developers to hook into the persisting process.
Removed stuff:
    
* `FOS\ElasticaBundle\DependencyInjection\Compiler\RegisterProvidersPass` class was removed. As well as `fos_elastica.provider` tag.
* `FOS\ElasticaBundle\Doctrine\AbstractProvider` class was removed.
* `FOS\ElasticaBundle\Doctrine\SliceFetcherInterface` class was removed.
* `FOS\ElasticaBundle\Doctrine\MongoDB\Provider` class was removed.
* `FOS\ElasticaBundle\Doctrine\MongoDB\SliceFetcher` class was removed.
* `FOS\ElasticaBundle\Doctrine\ORM\SliceFetcher` class was removed.
* `FOS\ElasticaBundle\Doctrine\ORM\Provider` class was removed.
* `FOS\ElasticaBundle\Doctrine\PHPCR\SliceFetcher` class was removed.
* `FOS\ElasticaBundle\Doctrine\PHPCR\Provider` class was removed.
* `FOS\ElasticaBundle\Propel\Provider` class was removed.
* `FOS\ElasticaBundle\Provider\AbstractProvider` class was removed.
* `FOS\ElasticaBundle\Provider\ProviderInterface` class was removed.
* `FOS\ElasticaBundle\Provider\ProviderRegistry` class was removed.
* `FOS\ElasticaBundle\Provider\ProviderRegistry` class was removed.
    

