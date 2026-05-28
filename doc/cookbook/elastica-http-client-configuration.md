Configuring Elastica HTTP client
================================

Setting HTTP Headers
--------------------

> **Deprecated since 7.1**: the top-level `headers` client option is deprecated.
> Move headers into `client_options` for your HTTP client (`headers` key for
> Guzzle/Symfony HTTP Client, `CURLOPT_HTTPHEADER` for elastic-transport's
> bundled Curl client).

It may be necessary to set HTTP headers on the Elastica client, for example an
Authorization header.

They can be set using the deprecated `headers` configuration key (still works,
applied via `Transport::setHeader` so it covers every HTTP client):

```yaml
# app/config/config.yml
fos_elastica:
    clients:
        default:
            hosts: ['http://example.com:80']
            headers:
                Authorization: "Basic jdumrGK7rY9TMuQOPng7GZycmxyMHNoir=="
```

HTTP Authentication
--------------------

It may be necessary to set HTTP Basic authorization on the Elastica client.

They can be set using the `username` and `password` configuration keys:

```yaml
# app/config/config.yml
fos_elastica:
    clients:
        default:
            hosts: ['http://example.com:80']
            username: 'The_user'
            password: 'Password!'
```

Setting SSL options
--------------------

It may be necessary to set SSL options on the Elastica client. These options are the same for Guzzle and Symfony HttpClient. 

They can be set using the `client_config` configuration key and following 4 options:

```yaml
# app/config/config.yml
fos_elastica:
    clients:
        default:
            hosts: ['http://example.com:80']
            client_config:
              ssl_cert: 'certificate'
              ssl_key: 'ssl key'
              ssl_verify: true
              ssl_ca: 'path/to/http_ca.crt'
```

Setting other client options
--------------------

`client_options` is a raw pass-through to the underlying HTTP client (Guzzle, Symfony HTTP Client, or elastic-transport's bundled Curl client). Configure it with whatever option names your client understands:

```yaml
# app/config/config.yml
fos_elastica:
    clients:
        default:
            hosts: ['http://example.com:80']
            client_options:
              # Guzzle / Symfony HTTP Client
              timeout: 30
              connect_timeout: 10
              proxy: 'http://localhost:8125'

              # elastic-transport's bundled Curl client (used when neither Guzzle nor
              # Symfony HTTP Client is installed). Use CURLOPT_* integer keys:
              !php/const \CURLOPT_TIMEOUT: 30
              !php/const \CURLOPT_CONNECTTIMEOUT: 10
              !php/const \CURLOPT_RANDOM_FILE: /dev/urandom
```

> **Deprecated since 7.1:** the top-level `timeout` client option is no longer applied — move it into `client_options` for your specific HTTP client (`timeout` for Guzzle/Symfony, `CURLOPT_TIMEOUT` for bundled Curl).
