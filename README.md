FOSElasticaBundle
=================

This bundle provides integration with [Elasticsearch](http://www.elasticsearch.org) and [Elastica](https://github.com/ruflin/Elastica) with
Symfony. Features include:

- Integrates the Elastica library into a Symfony environment
- Use JmsSerializer or Symfony Serializer to convert between PHP objects and Elasticsearch data
- Index configuration for Elasticsearch, or send data without configuration to use the dynamic mapping feature of Elasticsearch
- Listeners for Doctrine events for automatic indexing

[![Build Status](https://github.com/FriendsOfSymfony/FOSElasticaBundle/workflows/Continuous%20integration/badge.svg?branch=master)](https://github.com/FriendsOfSymfony/FOSElasticaBundle/actions?query=workflow%3A%22Continuous%20integration%22%20branch%3Amaster)
[![Total Downloads](https://poser.pugx.org/friendsofsymfony/elastica-bundle/downloads.png)](https://packagist.org/packages/friendsofsymfony/elastica-bundle)
[![Latest Stable Version](https://poser.pugx.org/friendsofsymfony/elastica-bundle/v/stable.png)](https://packagist.org/packages/friendsofsymfony/elastica-bundle)
[![Latest Unstable Version](https://poser.pugx.org/friendsofsymfony/elastica-bundle/v/unstable.svg)](https://packagist.org/packages/friendsofsymfony/elastica-bundle)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/FriendsOfSymfony/FOSElasticaBundle/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/FriendsOfSymfony/FOSElasticaBundle/?branch=master)

Documentation
-------------

Documentation for FOSElasticaBundle is in [`doc/index.md`](doc/index.md)

Installation
------------

Installation instructions can be found in the [documentation](doc/setup.md)

Versions & Dependencies
-----------------------

Version 6 of the FOSElasticaBundle is compatible with Elasticsearch 7. It requires Symfony 4.4 or greater. When using
Symfony Flex there is also a [recipe to ease the setup](https://github.com/symfony/recipes-contrib/tree/master/friendsofsymfony/elastica-bundle/5.0).
Earlier versions of the FOSElasticaBundle are not maintained anymore and only work with older versions of the dependencies.
The following table shows the compatibilities of different versions of the bundle.

| FOSElasticaBundle                                                                       | Elastica | Elasticsearch | Symfony    | PHP   |
| --------------------------------------------------------------------------------------- | ---------| ------------- | ---------- | ----- |
| [6.0] (master)                                                                          | ^7.1     | 7.\*          | ^4.4\|^5.3 | >=7.4 |
| [5.1] (5.1.x)                                                                           | ^5.3\|^6 | 5.\*\|6.\*    | ^3.4\|^4   | >=7.1 |
| [5.0] (unmaintained)                                                                    | ^5.2\|^6 | 5.\*\|6.\*    | ^3.2\|^4   | >=5.6 |
| [4.x] (unmaintained)                                                                    | 3.2.\*   | 2.\*          | ^2.8\|^3.2 | >=5.5 |
| [3.2.x] (unmaintained)                                                                  | ^2.1     | 1.\*          | ^2.3\|^3   | >=5.3 |

License
-------

This bundle is released under the MIT license. See the included [LICENSE](LICENSE) file for more information.
