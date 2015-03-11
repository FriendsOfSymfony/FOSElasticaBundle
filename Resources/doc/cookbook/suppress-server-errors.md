Suppressing Server Errors
=========================

By default, exceptions from the Elastica client library will propagate through
the bundle's Client class. For instance, if the Elasticsearch server is offline,
issuing a request will result in an `Elastica\Exception\Connection` being thrown.
Depending on your needs, it may be desirable to suppress these exceptions and
allow searches to fail silently.

One way to achieve this is to override the `fos_elastica.client.class` service
container parameter with a custom class. In the following example, we override
the `Client::request()` method and return the equivalent of an empty search
response if an exception occurred.

Sample client code:
-------------------

```php
<?php

namespace Acme\ElasticaBundle;

use Elastica\Exception\ExceptionInterface;
use Elastica\Request;
use Elastica\Response;
use FOS\ElasticaBundle\Elastica\Client as BaseClient;

class Client extends BaseClient
{
    public function request($path, $method = Request::GET, $data = array(), array $query = array())
    {
        try {
            return parent::request($path, $method, $data, $query);
        } catch (ExceptionInterface $e) {
            if ($this->_logger) {
                $this->_logger->warning('Failed to send a request to ElasticSearch', array(
                    'exception' => $e->getMessage(),
                    'path' => $path,
                    'method' => $method,
                    'data' => $data,
                    'query' => $query
                ));
            }

            return new Response('{"took":0,"timed_out":false,"hits":{"total":0,"max_score":0,"hits":[]}}');
        }
    }
}
```

Configuration change:
---------------------

You must update a parameter in your `app/config/config.yml` file to point to your overridden client:

```yaml
parameters:
    fos_elastica.client.class: Acme\ElasticaBundle\Client
```
