CHANGELOG for 4.1.x
===================

This changelog references the relevant changes (bug and security fixes) done
in 4.1 versions.

## 4.1.2 (2018-01-03)

* Fixed Doctrine registry in `MongoDB` and `PHPCR` pager providers.

## 4.1.1 (2017-12-20)

* Unset options of new provider API when an old one is used.

## 4.1.0 (2017-12-18)

* Introduce a new provider's API. Deprecate legacy ones. See #1240
* Introduce a provider pager. 
* Introduce InPlacePagerPersister.
* Add new options to populate command: --first-page, last-page, --max-per-page. They work only if you use v5 providers API.
* Deprecate some options of populate command: --batch-size and --offset.
* Deprecate Propel support
* Cast value objects used as identifier in Elasticsearch to string
