Http compression
==========================================

By default, FOSElasticaBundle and elastica do not compress the http request but you can do it with a simple configuration:

```yaml
# app/config/config.yml
fos_elastica:
    clients:
        default:
            host: example.com
            compression: true
```
