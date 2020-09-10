Using a Serializer in FOSElasticaBundle
=======================================

FOSElasticaBundle supports using a Serializer component to serialize your objects to JSON
which will be sent directly to the Elasticsearch server. Combined with automatic mapping
it means types do not have to be mapped.

A) Install and declare the serializer
-------------------------------------

Both the [Symfony Serializer](http://symfony.com/doc/current/components/serializer.html) and 
[JMSSerializerBundle](http://jmsyst.com/bundles/JMSSerializerBundle) are supported.

Enable the Symfony serializer in the configuration:

```yaml
#app/config/config.yml
fos_elastica:
    serializer: ~
```

Alternatively the JMS serializer can be used as follows:

```yaml
#app/config/config.yml
fos_elastica:
    serializer:
        serializer: jms_serializer
```

B) Set up each defined type to support serialization
----------------------------------------------------

An index does not need to have mappings defined when using a serializer. An example configuration
for an index in this case:

```yaml
fos_elastica:
    indexes:
        app:
            serializer:
                groups: [elastica]
            types:
                user:
```

And inside the User class:

```php
use Symfony\Component\Serializer\Annotation\Groups;

class User {

    /**
     * @Groups({"elastica"})
     *
     * @var string
     */
    private $username;
    
}
```

In addition the JMS Serializer allows you to specify options for version and whether to serialize null

```yaml
fos_elastica:

    indexes:
        app:
            serializer:
                groups: [elastica]
                version: '1.1'
                serialize_null: true
            types:
                user:
```
