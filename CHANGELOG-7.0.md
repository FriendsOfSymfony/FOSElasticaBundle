* Dropped support for PHP 7.4 and PHP 8.0.
* Dropped support for Symfony 5.4.
* **[BC break]** Method `FOS\ElasticaBundle\Elastica\Client::request` does not exist anymore. Please use `FOS\ElasticaBundle\Elastica\Client::sendRequest`.
* **[BC break]** Method `FOS\ElasticaBundle\Elastica\Client::getIndex` now returns `FOS\ElasticaBundle\Elastica\Index`.
* **[BC break]** Arguments for the service `FOS\ElasticaBundle\Elastica\Client` have changed. See definition of `FOS\ElasticaBundle\Elastica\Client::__construct`.
