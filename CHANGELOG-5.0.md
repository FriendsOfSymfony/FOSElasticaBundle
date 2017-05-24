CHANGELOG for 5.0.x
===================

This changelog references the relevant changes (bug and security fixes) done
in 5.0 versions.

### 5.0.0 (2017-xx-xx)

* Add `ruflin/elastica` 5.x support.
* Add asnychronous index update option.
* Add ability to close an index.
* Add support for HTTP authentication.
* Fix undefined index when `ignore_missing` is active.
* Dropped PHP 5.5 support.
* Removed Symfony 3.0 support.
* Removed all `fos_elastica.*.class` parameters. Overwrite or decorate the service instead if you
   need to change the definition.
* [BC break] Removed `_boost`, `ttl` and `timestamp` config options.
* [BC break] Removed deprecated config options `servers`, `mappings` and `is_indexable_callback`.
* [BC break] Add `PaginatedFinderInterface::createRawPaginatorAdapter`.
* [BC break] Add `PaginatorAdapterInterface::getMaxScore`.
