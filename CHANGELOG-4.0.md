CHANGELOG for 4.0.x
===================

This changelog references the relevant changes (bug and security fixes) done
in 4.0 versions.

### 4.0.1 (2017-08-10)

* Fixed Propel object sorting
* Fixed the profiler icon for IE

### 4.0.0 (2017-03-30)

* Add `ruflin/Elastica` 3.x support.
* Add new repository manager.
* Add support for `DateTimeInterface` in `ModelToElasticaAutoTransformer`.
* Add support for suggesters.
* Dropped PHP 5.3 and 5.4 support.
* Removed Symfony < 2.7 support.
* [BC break] Allow additional parameters to `AbstractProvider::queryBuilder`.
* [BC break] Added `PaginatorAdapterInterface::getSuggests`.
* [BC break] Removed faceted search support.
* [BC break] Removed `AbstractProvider::isObjectIndexable`.
* [BC break] Removed `AbstractProvider::getMemoryUsage`.
* [BC break] Removed deprecated `Resetter` class.
* [BC break] Removed deprecated `Client` class.
* [BC break] Removed deprecated `IndexManager` class.
* [BC break] Removed deprecated `DynamicIndex` class.
* [BC break] Removed deprecated `immediate` configuration option.
* [BC break] Removed `Search` annotations as they cannot be used anymore.
* [BC break] Removed `TransformedFinder::moreLikeThis`.
* [BC break] Removed `ignore_unmapped` support for Paginator.
