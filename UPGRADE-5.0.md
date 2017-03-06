UPGRADE FROM 4.0 to 5.0
=======================

### Elastica library changes
Elastica has been updated to version 5.1. Please consider [their changelog for an upgrade guide](https://github.com/ruflin/Elastica/blob/master/CHANGELOG.md#510).

### API Changes
  * Removed `sortIgnoreUnmapped` support for Paginator. `ignore_unmapped` is not supported in ES 5.0 anymore.
  * Removed `ttl` and `timestamp` support in configuration. These attributes are not supported in ES 5.0 anymore.
  * Removed `TransformedFinder::moreLikeThis`.
