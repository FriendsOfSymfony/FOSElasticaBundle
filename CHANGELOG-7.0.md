CHANGELOG for 7.x
===================

This changelog references the relevant changes (bug and security fixes) done
in 7.x versions.

### 7.0.0-BETA1 (2025-0X-XX)

* Dropped support for PHP 7.4 and PHP 8.0.
* Dropped support for Symfony 5.4.
* **[BC break]** Method `FOS\ElasticaBundle\Elastica\Client::request` does not exist anymore. Please use `FOS\ElasticaBundle\Elastica\Client::sendRequest`.
* **[BC break]** Method `FOS\ElasticaBundle\Elastica\Client::getIndex` now returns `FOS\ElasticaBundle\Elastica\Index`.
* **[BC break]** Arguments for the service `FOS\ElasticaBundle\Elastica\Client` have changed. See definition of `FOS\ElasticaBundle\Elastica\Client::__construct`.
* **[BC break]** Client configuration now reflects configuration of `Elastica\Client`.
* **[BC break]** Index template configuration `index_template` option `template` is renamed to `index_patterns` and accepts array of strings.
* **[BC break]** Arguments for the service `FOS\ElasticaBundle\Elastica\Client` (`fos_elastica.client..`) are now named, instead of indexed.
* **[BC break]** Configuration options: `host`, `port`, `url` are no longer available and replaced with single `hosts`.
* **[BC break]** Configuration options: `proxy`, `auth_type`, `aws_*`, `ssl`, `curl`, `persistent`, `compression`, `connectTimeout` are no longer available.
* **[BC break]** Configuration `connectionStrategy` is renamed to `connection_strategy`.

Main change is the configuration of the bundle:
* There are no `connections` level anymore.
* Options `host`, `port` and `url` are replaced with option `hosts`, which accepts array.
* SSL configuration is provided within `client_config` option.
* Other client options are configurable in `client_options`.
* Refer to new examples! [Elastica HTTP client configuration](doc/cookbook/elastica-http-client-configuration.md)
