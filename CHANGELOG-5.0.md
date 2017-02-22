CHANGELOG for 5.0.x
===================

This changelog references the relevant changes (bug and security fixes) done
in 5.0 versions.

* 5.0.0 (xxxx-xx-xx)
 * Add `ruflin/elastica` 5.1 support.
 * Removed PHP < 5.6 support.
 * Removed Symfony <2.7 and =3.0 support.
 * Removed `sortIgnoreUnmapped` support for Paginator. `ignore_unmapped` is not supported in ES 5.0 anymore.
 * Removed `ttl` and `timestamp` support in configuration. These attributes are not supported in ES 5.0 anymore.
