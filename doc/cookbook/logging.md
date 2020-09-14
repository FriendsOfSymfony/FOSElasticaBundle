Logging and its performance considerations
==========================================

By default, FOSElasticaBundle sets a logger against each Elastica client configured and
logs all information sent to and received from Elasticsearch. This can lead to large
memory usage during population or reindexing of an index.

By default, FOSElasticaBundle will only enable a logger when debug mode is enabled, meaning
in a production environment there won't be a logger enabled. To enable a logger anyway, you
can set the logger property of a client configuration to `true` or a service id of a logging
service you wish to use.

```yaml
# app/config/config.yml
fos_elastica:
    clients:
        default:
            host: example.com
            logger: true
```

Custom Logger Service
---------------------

It is also possible to specify a custom logger instance to be injected into each client by
specifying the service id of the logger you wish to use.

```yaml
# app/config/config.yml
fos_elastica:
    clients:
        default:
            host: example.com
            logger: 'acme.custom.logger'
```
