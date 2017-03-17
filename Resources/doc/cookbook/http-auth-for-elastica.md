Setting HTTP Auth on the Elastica Client
===========================================

It may be necessary to set username/password for HTTP authentication on the Elastica client.

They can be set using the username and password configuration key:

```yaml
# app/config/config.yml
fos_elastica:
    clients:
        default:
            host: example.com
            port: 80
            username: 'username'
            password: 'password'
```
