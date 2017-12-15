HTTP compression
==========================================

By default, FOSElasticaBundle and Elastica do not compress the HTTP request but you can do it with a simple configuration:

```yaml
# app/config/config.yml
fos_elastica:
    clients:
        default:
            host: example.com
            compression: true
```
