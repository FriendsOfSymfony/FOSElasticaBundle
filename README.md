FOSElasticaBundle
=================

This bundle provides integration with [Elasticsearch](http://www.elasticsearch.org) and [Elastica](https://github.com/ruflin/Elastica) with
Symfony. Features include:

- Integrates the Elastica library into a Symfony environment
- Use JmsSerializer or Symfony Serializer to convert between PHP objects and Elasticsearch data
- Index configuration for Elasticsearch, or send data without configuration to use the dynamic mapping feature of Elasticsearch
- Listeners for Doctrine events for automatic indexing

[![Build Status](https://github.com/FriendsOfSymfony/FOSElasticaBundle/actions/workflows/continuous-integration.yaml/badge.svg)](https://github.com/FriendsOfSymfony/FOSElasticaBundle/actions)
[![Total Downloads](https://poser.pugx.org/friendsofsymfony/elastica-bundle/downloads.png)](https://packagist.org/packages/friendsofsymfony/elastica-bundle)
[![Latest Stable Version](https://poser.pugx.org/friendsofsymfony/elastica-bundle/v/stable.png)](https://packagist.org/packages/friendsofsymfony/elastica-bundle)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/FriendsOfSymfony/FOSElasticaBundle/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/FriendsOfSymfony/FOSElasticaBundle/?branch=master)

Documentation
-------------

Documentation for FOSElasticaBundle is in [`doc/index.md`](doc/index.md)

Installation
------------

Installation instructions can be found in the [documentation](doc/setup.md)

Versions & Dependencies
-----------------------

Version 7 of the FOSElasticaBundle is compatible with Elasticsearch 8 and 9. It requires Symfony 6.4 or greater. When using
Symfony Flex there is also a [recipe to ease the setup](https://github.com/symfony/recipes-contrib/tree/master/friendsofsymfony/elastica-bundle/5.0).
Earlier versions of the FOSElasticaBundle are not maintained anymore and only work with older versions of the dependencies.
The following table shows the compatibilities of different versions of the bundle.

| FOSElasticaBundle | Elastica         | Elasticsearch | Symfony    | PHP   |
|-------------------|------------------|---------------| ---------- | ----- |
| [7.2]             | ^8.0 \|\| ^9.0   | 8.\* \|\| 9.\* | ^6.4\|^7.4\|^8.0 | ^8.1 |

License
-------

This bundle is released under the MIT license. See the included [LICENSE](LICENSE) file for more information.
