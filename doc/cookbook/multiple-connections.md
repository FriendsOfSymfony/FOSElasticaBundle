Multiple Connections
====================

You can define multiple endpoints for an Elastica client by specifying them as
multiple connections in the client configuration:

```yaml
fos_elastica:
    clients:
        default:
            connections:
                - url: http://es1.example.net:9200
                - url: http://es2.example.net:9200
            connection_strategy: RoundRobin
```

Elastica allows for definition of different connection strategies and by default
supports `RoundRobin` and `Simple`. You can see definitions for these strategies
in the `Elastica\Connection\Strategy` namespace.

For more information on Elastica clustering see http://elastica.io/getting-started/installation.html#section-connect-cluster
