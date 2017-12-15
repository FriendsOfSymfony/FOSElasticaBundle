Setting HTTP Headers on the Elastica Client
===========================================

It may be necessary to set HTTP headers on the Elastica client, for example an
Authorization header.

They can be set using the headers configuration key:

```yaml
# app/config/config.yml
fos_elastica:
    clients:
        default:
            host: example.com
            port: 80
            headers:
                Authorization: "Basic jdumrGK7rY9TMuQOPng7GZycmxyMHNoir=="
```
