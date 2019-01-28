CHANGELOG for 5.1.x
===================

This changelog references the relevant changes (bug and security fixes) done
in 5.1 versions.

### 5.1.0 (2019-xx-xx)

* Added compatibility with Symfony 4.2.
* Added autowiring support for `Elastica\Client`.
* Added Pagerfanta 2.0 support.
* Added optional routing in `ObjectPersister::deleteById`.
* Added refresh options for persisters.
* Added index name to `TransformEvent` document.
* Added many unit tests.
* Handle multiline strings in `ElasticaLogger`.
* Fixed pagination of ORM queries when populating.
* Profile panel redesign.
* Updated documentation files.
* Dropped PHP 5.6 support.
