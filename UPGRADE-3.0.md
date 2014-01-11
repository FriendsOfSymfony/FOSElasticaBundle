UPGRADE FROM 2.1 to 3.0
=======================

### Serialization

  * you can now define a Serializer service and callback for indexing. All providers and listeners will use it.

    ```yml
    serializer:
        callback_class: FOS\ElasticaBundle\Serializer\Callback
        serializer: serializer
    ```

### Mapping

  * you do not have to setup any mapping anymore if you use a Serializer, properties are no more indexed only if
    they are mapped. So this kind of configuration became valid:

    ```yml
    serializer:
        callback_class: FOS\ElasticaBundle\Serializer\Callback
        serializer: serializer
    indexes:
        acme:
            client: default
            types:
                Article:
                    persistence:
                        driver: orm
                        model:  Acme\Bundle\CoreBundle\Entity\Article
                        provider: ~
    ```
