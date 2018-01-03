CHANGELOG for 5.0.x
===================

This changelog references the relevant changes (bug and security fixes) done
in 5.0 versions.

### 5.0.2 (2018-01-03)

* Fixed Doctrine registry in `MongoDB` and `PHPCR` pager providers.
* Unset options of new provider API when an old one is used.
* Fixed `fos_elastica.paginator.subscriber` service to be public.

### 5.0.1 (2017-12-20)

* Fix typo in populate command: option first-page.

### 5.0.0 (2017-12-18)

* Add `ruflin/elastica` 5.x and 6.x support.
* Add asnychronous index update option.
* Add ability to close an index.
* Add support for HTTP authentication.
* Fix undefined index when `ignore_missing` is active.
* Dropped PHP 5.5 support.
* Removed Symfony 2.7, 2.8, 3.0 and 3.1 support.
* Added Symfony 4 support.
* Made commands services and add support for lazy loading of them
* Removed all `fos_elastica.*.class` parameters. Overwrite or decorate the service instead if you
   need to change the definition.
* [BC break] Removed `_boost`, `ttl` and `timestamp` config options.
* [BC break] Removed deprecated config options `servers`, `mappings` and `is_indexable_callback`.
* [BC break] Add `PaginatedFinderInterface::createRawPaginatorAdapter`.
* [BC break] Add `PaginatorAdapterInterface::getMaxScore`.
* [BC break] Removed Propel support.
* [BC break] Removed `offset` and `batch-size` options of the populate command.
    Use `first-page`, `last-page` and `max-per-page` instead.
