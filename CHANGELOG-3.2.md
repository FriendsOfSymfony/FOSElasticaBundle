CHANGELOG for 3.2.x
===================

* 3.2.0 (Unreleased)

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
