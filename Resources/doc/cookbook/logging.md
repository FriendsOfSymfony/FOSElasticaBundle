Logging and its performance considerations
==========================================

By default, FOSElasticaBundle sets a logger against each Elastica client configured and
logs all information sent to and received from Elasticsearch. This can lead to large
memory usage during population or reindexing of an index.

FOSElasticaBundle provides an option to disable a logger by setting the property on the
client configuration to false:

```yaml
# app/config/config.yml
fos_elastica:
    clients:
        default:
            host: example.com
            logger: false
```

It may be desirable to set this configuration property to `%kernel.debug%`, which would
only switch the logging capabilities of FOSElasticaBundle on when debugging is enabled.

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
