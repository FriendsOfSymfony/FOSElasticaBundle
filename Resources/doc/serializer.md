Using a Serializer in FOSElasticaBundle
=======================================

FOSElasticaBundle supports using a Serializer component to serialize your objects to JSON
which will be sent directly to the Elasticsearch server. Combined with automatic mapping
it means types do not have to be mapped.

A) Install and declare the serializer
-------------------------

Follow the installation instructions for [JMSSerializerBundle](http://jmsyst.com/bundles/JMSSerializerBundle).

Enable the serializer configuration for the bundle:

```yaml
#app/config/config.yml
fos_elastica:
    serializer: ~
```

The default configuration that comes with FOSElasticaBundle supports both the JMS Serializer
and the Symfony Serializer. If JMSSerializerBundle is installed, additional support for
serialization groups and versions are added to the bundle.

B) Set up each defined type to support serialization
----------------------------------------------------

A type does not need to have mappings defined when using a serializer. An example configuration
for a type in this case:

```yaml
fos_elastica:
    indexes:
        search:
            types:
                user:
                    serializer:
                        groups: [elastica, Default]
```
