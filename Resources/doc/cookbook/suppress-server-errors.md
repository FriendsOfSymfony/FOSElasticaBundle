Suppressing Server Errors
========================

By default, exceptions from the Elastica client library will propagate through
the bundle's Client class. For instance, if the Elasticsearch server is offline,
issuing a request will result in an `Elastica\Exception\Connection` being thrown.
Depending on your needs, it may be desirable to suppress these exceptions and
allow searches to fail silently.

One way to achieve this is to override the `fos_elastica.client.class` service
container parameter with a custom class. In the following example, we override
the `Client::request()` method and return the equivalent of an empty search
response if an exception occurred.

```
<?php

namespace Acme\ElasticaBundle;

use FOS\ElasticaBundle\Client as BaseClient;

use Elastica\Exception\ExceptionInterface;
use Elastica\Response;

class Client extends BaseClient
{
    public function request($path, $method, $data = array())
    {
        try {
            return parent::request($path, $method, $data);
        } catch (ExceptionInterface $e) {
            return new Response('{"took":0,"timed_out":false,"hits":{"total":0,"max_score":0,"hits":[]}}');
        }
    }
}
```