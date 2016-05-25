Using a Serializer in FOSElasticaBundle
=======================================

FOSElasticaBundle supports using a Serializer component to serialize your objects to JSON
which will be sent directly to the Elasticsearch server. Combined with automatic mapping
it means types do not have to be mapped.

A) Install and declare the serializer
-------------------------------------

Both the [Symfony Serializer](http://symfony.com/doc/current/components/serializer.html) and 
[JMSSerializerBundle](http://jmsyst.com/bundles/JMSSerializerBundle) are supported.

If you want to override the default serializer service you can do so in the configuration:

```yaml
#app/config/config.yml
fos_elastica:
    serializer: ~
```

B) Set up each defined type to support serialization
----------------------------------------------------

A type does not need to have mappings defined when using a serializer. An example configuration
for a type in this case:

```yaml
fos_elastica:
    indexes:
        app:
            types:
                user:
                    serializer:
                        groups: [elastica]
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
            types:
                user:
                    serializer:
                        groups: [elastica]
                        version: '1.1'
                        serialize_null: true
```