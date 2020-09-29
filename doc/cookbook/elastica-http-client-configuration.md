Configuring Elastica HTTP client
================================

Setting HTTP Headers
--------------------

It may be necessary to set HTTP headers on the Elastica client, for example an
Authorization header.

They can be set using the `headers` configuration key:

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

Setting cURL options
--------------------

It may be necessary to set cURL options on the Elastica client.

They can be set using the `curl` configuration key:

```yaml
# app/config/config.yml
fos_elastica:
    clients:
        default:
            host: example.com
            port: 80
            curl:
                !php/const \CURLOPT_SSL_VERIFYPEER: false
```
