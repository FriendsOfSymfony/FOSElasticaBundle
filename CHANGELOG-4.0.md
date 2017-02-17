CHANGELOG for 4.0.x
===================

This changelog references the relevant changes (bug and security fixes) done
in 4.0 versions.

* 4.0.0 (xxxx-xx-xx)

 * Add `ruflin/Elastica` 5.1 support.
 * Allow additional parameters to `AbstractProvider::queryBuilder`.
 * Removed PHP < 5.6 support.
 * Removed Symfony <2.7 and =3.0 support.
 * Removed faceted search support.
 * Removed `AbstractProvider::isObjectIndexable`.
 * Removed `AbstractProvider::getMemoryUsage`.
 * Removed deprecated `Resetter` class.
 * Removed deprecated `Client` class.
 * Removed deprecated `IndexManager` class.
 * Removed deprecated `DynamicIndex` class.
 * Removed `sortIgnoreUnmapped` support for Paginator. `ignore_unmapped` is not supported in ES 5.0 anymore.
 * Removed `ttl` and `timestamp` support in configuration. These attributes are not supported in ES 5.0 anymore.
