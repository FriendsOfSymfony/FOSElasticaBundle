FOSElasticaBundle
=================

This bundle provides integration with [Elasticsearch](http://www.elasticsearch.org) and [Elastica](https://github.com/ruflin/Elastica) with
Symfony. Features include:

- Integrates the Elastica library into a Symfony environment
- Automatically generate mappings using a serializer
- Listeners for Doctrine events for automatic indexing

[![Build Status](https://secure.travis-ci.org/FriendsOfSymfony/FOSElasticaBundle.png?branch=master)](http://travis-ci.org/FriendsOfSymfony/FOSElasticaBundle) [![Total Downloads](https://poser.pugx.org/FriendsOfSymfony/elastica-bundle/downloads.png)](https://packagist.org/packages/FriendsOfSymfony/elastica-bundle) [![Latest Stable Version](https://poser.pugx.org/FriendsOfSymfony/elastica-bundle/v/stable.png)](https://packagist.org/packages/FriendsOfSymfony/elastica-bundle) [![Latest Unstable Version](https://poser.pugx.org/friendsofsymfony/elastica-bundle/v/unstable.svg)](https://packagist.org/packages/friendsofsymfony/elastica-bundle)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/FriendsOfSymfony/FOSElasticaBundle/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/FriendsOfSymfony/FOSElasticaBundle/?branch=master)

Documentation
-------------

Documentation for FOSElasticaBundle is in [`doc/index.md`](doc/index.md)

Installation
------------

Installation instructions can be found in the [documentation](doc/setup.md)

Versions & Dependencies
-----------------------

Version 5 of the FOSElasticaBundle is compatible with Elasticsearch 5 and 6. It requires Symfony 3 or 4. When using
Symfony Flex there is also a [recipe to ease the setup](https://github.com/symfony/recipes-contrib/tree/master/friendsofsymfony/elastica-bundle/5.0).
Earlier versions of the FOSElasticaBundle are not maintained anymore and only work with older versions of the dependencies.
The following table shows the compatibilities of different versions of the bundle.

| FOSElasticaBundle                                                                       | Elastica | Elasticsearch | Symfony    | PHP   |
| --------------------------------------------------------------------------------------- | ---------| ------------- | ---------- | ----- |
| [5.1] (master)                                                                          | ^5.3\|^6 | 5.\*\|6.\*    | ^3.4\|^4   | >=7.1 |
| [5.0] (unmaintained)                                                                    | ^5.2\|^6 | 5.\*\|6.\*    | ^3.2\|^4   | >=5.6 |
| [4.x] (unmaintained)                                                                    | 3.2.\*   | 2.\*          | ^2.8\|^3.2 | >=5.5 |
| [3.2.x] (unmaintained)                                                                  | ^2.1     | 1.\*          | ^2.3\|^3   | >=5.3 |

License
-------

This bundle is released under the MIT license. See the included [LICENSE](LICENSE) file for more information.
