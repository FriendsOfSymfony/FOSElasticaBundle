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

Any other client option for Elastica client can be set using the `client_options` configuration key:

```yaml
# app/config/config.yml
fos_elastica:
    clients:
        default:
            hosts: ['http://example.com:80']
            client_options:
              !php/const \CURLOPT_RANDOM_FILE: /dev/urandom
              proxy: 'http://localhost:8125'
              connect_timeout: 10 # if using Guzzle
```
