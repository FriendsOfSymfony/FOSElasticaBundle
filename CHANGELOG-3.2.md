CHANGELOG for 3.2.x
===================

This changelog references the relevant changes (bug and security fixes) done
in 3.2 versions.

* 3.2.1 (2016-xx-xx)

 * Restored and deprecated `immediate` option

* 3.2.0 (2016-08-04)

 * Allow driverless type definitions #953
 * Change Elastica constraints to allow ~2.1 as Elastica now follows semver
 * Add support for the [dynamic](https://www.elastic.co/guide/en/elasticsearch/reference/current/dynamic.html) setting in mapping
 * Fixed PropelCollection to array casting error #992
 * Allow set retryOnConflict per connection
 * New event `PRE_TRANSFORM` which allows developers to modify objects before
   transformation into documents for indexing
 * Introduce `serialize_null` option for Serializer
 * Ability to specify custom connection settings for functional tests
 * Doctrine: possible to use hints when hydrating objects
 * Resolved Propel configuration
 * Add Elastica compression option
 * Add support for `defaultSortFieldName` and `defaultSortDirection` pagination options
 * Removed `immediate` option on type persistence configuration
 * Enable pagination of hybrid results
 * Add Symfony Serializer support
