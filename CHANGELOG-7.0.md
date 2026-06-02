CHANGELOG for 7.x
===================

This changelog references the relevant changes (bug and security fixes) done
in 7.x versions.

### 7.2.1 (2026-xx-xx)
* Added priority flag for index templates.

### 7.2.0 (2026-06-01)
* Added Elasticsearch 9 and Elastica 9 support.
* `headers` client config is now applied via `Transport::setHeader()` so it works uniformly across Guzzle, Symfony HTTP Client, and elastic-transport's bundled Curl client.
* `timeout` client config is now translated to `CURLOPT_TIMEOUT` at runtime when the active transport client is elastic-transport's bundled Curl — Guzzle/Symfony HTTP Client keep consuming the original `'timeout'` key as before.
* Default config no longer injects `'headers' => []` / `'timeout' => 30` into `http_client_options`, which would otherwise break elastic-transport's bundled Curl client (PHP 8+ `ValueError` on unknown `curl_setopt_array` keys).
* **Deprecated** the top-level `headers` and `timeout` client config. Move them into `client_options` — for Guzzle/Symfony HTTP Client use `headers` / `timeout`, for the bundled Curl client use `CURLOPT_HTTPHEADER` / `CURLOPT_TIMEOUT`.
* Fix deprecated Symfony method call.
* Instantiate custom repositories using the DI service locator.
* Add compatibility with `doctrine/doctrine-bundle` 3.x, `doctrine/phpcr-odm` 3.x and Symfony 8.0 in highest-deps CI.
* Raise minimum versions of `doctrine/doctrine-bundle` (^2.5), `doctrine/orm` (^2.17) and `jms/serializer-bundle` (^5.4) to fix lowest-deps CI.

### 7.1.0 (2026-04-24)
* Added Symfony 8.0 support.
* Added PHP 8.5 test coverage.
* Dropped Symfony 7.1, 7.2 and 7.3 support.

### 7.0.0 (2025-10-20)

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
* **[BC break]** Event `PostElasticaRequestEvent` now operates with `Psr\Http\Message\RequestInterface` instead of `Elastica\Request`.
* **[BC break]** Event `ElasticaRequestExceptionEvent` now operates with `Psr\Http\Message\RequestInterface` instead of `Elastica\Request`, and `Elastic\Elasticsearch\Exception\ElasticsearchException` instead of `Elastica\Exception\ExceptionInterface`.

Main change is the configuration of the bundle:
* There are no `connections` level anymore.
* Options `host`, `port` and `url` are replaced with option `hosts`, which accepts array.
* SSL configuration is provided within `client_config` option.
* Other client options are configurable in `client_options`.
* Refer to new examples! [Elastica HTTP client configuration](doc/cookbook/elastica-http-client-configuration.md)
