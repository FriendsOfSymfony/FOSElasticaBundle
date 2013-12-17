Type configuration
==================

Handling missing results with FOSElasticaBundle
-----------------------------------------------

By default, FOSElasticaBundle will throw an exception if the results returned from
Elasticsearch are different from the results it finds from the chosen persistence
provider. This may pose problems for a large index where updates do not occur instantly
or another process has removed the results from your persistence provider without
updating Elasticsearch.

The error you're likely to see is something like:
'Cannot find corresponding Doctrine objects for all Elastica results.'

To solve this issue, each type can be configured to ignore the missing results:

```yaml
                user:
                    persistence:
                        elastica_to_model_transformer:
                            ignore_missing: true
```

Dynamic templates
-----------------

Dynamic templates allow to define mapping templates that will be
applied when dynamic introduction of fields / objects happens.

[Documentation](http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/mapping-root-object-type.html#_dynamic_templates)

```yaml
fos_elastica:
    indexes:
        site:
            types:
                user:
                    dynamic_templates:
                        my_template_1:
                            match: apples_*
                            mapping:
                                type: float
                        my_template_2:
                            match: *
                            match_mapping_type: string
                            mapping:
                                type: string
                                index: not_analyzed
                    mappings:
                        username: { type: string }
```

Nested objects in FOSElasticaBundle
-----------------------------------

Note that object can autodetect properties

```yaml
fos_elastica:
    indexes:
        website:
            types:
                post:
                    mappings:
                        date: { boost: 5 }
                        title: { boost: 3 }
                        content: ~
                        comments:
                            type: "nested"
                            properties:
                                date: { boost: 5 }
                                content: ~
                        user:
                            type: "object"
                        approver:
                            type: "object"
                            properties:
                                date: { boost: 5 }
```

Parent fields
-------------

```yaml
fos_elastica:
    indexes:
        website:
            types:
                comment:
                    mappings:
                        date: { boost: 5 }
                        content: ~
                    _parent:
                        type: "post"
                        property: "post"
                        identifier: "id"
```

The parent field declaration has the following values:

 * `type`: The parent type.
 * `property`: The property in the child entity where to look for the parent entity. It may be ignored if is equal to
  the parent type.
 * `identifier`: The property in the parent entity which has the parent identifier. Defaults to `id`.

Note that to create a document with a parent, you need to call `setParent` on the document rather than setting a
_parent field. If you do this wrong, you will see a `RoutingMissingException` as Elasticsearch does not know where
to store a document that should have a parent but does not specify it.

Date format example
-------------------

If you want to specify a [date format](http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/mapping-date-format.html):

```yaml
                user:
                    mappings:
                        username: { type: string }
                        lastlogin: { type: date, format: basic_date_time }
                        birthday: { type: date, format: "yyyy-MM-dd" }
```

Custom settings
---------------

Any setting can be specified when declaring a type. For example, to enable a custom
analyzer, you could write:

```yaml
    indexes:
        search:
            settings:
                index:
                    analysis:
                        analyzer:
                            my_analyzer:
                                type: custom
                                tokenizer: lowercase
                                filter   : [my_ngram]
                        filter:
                            my_ngram:
                                type: "nGram"
                                min_gram: 3
                                max_gram: 5
            types:
                blog:
                    mappings:
                        title: { boost: 8, analyzer: my_analyzer }
```

Provider Configuration
----------------------

### Specifying a custom query builder for populating indexes

When populating an index, it may be required to use a different query builder method
to define which entities should be queried.

```yaml
                user:
                    persistence:
                        provider:
                            query_builder_method: createIsActiveQueryBuilder
```

### Populating batch size

By default, ElasticaBundle will index documents by packets of 100.
You can change this value in the provider configuration.

```yaml
                user:
                    persistence:
                        provider:
                            batch_size: 10
```

### Changing the document identifier

By default, ElasticaBundle will use the `id` field of your entities as
the Elasticsearch document identifier. You can change this value in the
persistence configuration.

```yaml
                user:
                    persistence:
                        identifier: searchId
```

Listener Configuration
----------------------

### Realtime, selective index update

If you use the Doctrine integration, you can let ElasticaBundle update the indexes automatically
when an object is added, updated or removed. It uses Doctrine lifecycle events.
Declare that you want to update the index in real time:

```yaml
                user:
                    persistence:
                        driver: orm
                        model: Application\UserBundle\Entity\User
                        listener: ~ # by default, listens to "insert", "update" and "delete"
```

Now the index is automatically updated each time the state of the bound Doctrine repository changes.
No need to repopulate the whole "user" index when a new `User` is created.

You can also choose to only listen for some of the events:

```yaml
                    persistence:
                        listener:
                            insert: true
                            update: false
                            delete: true
```

> **Propel** doesn't support this feature yet.

### Checking an entity method for listener

If you use listeners to update your index, you may need to validate your
entities before you index them (e.g. only index "public" entities). Typically,
you'll want the listener to be consistent with the provider's query criteria.
This may be achieved by using the `is_indexable_callback` config parameter:

```yaml
                    persistence:
                        listener:
                            is_indexable_callback: "isPublic"
```

If `is_indexable_callback` is a string and the entity has a method with the
specified name, the listener will only index entities for which the method
returns `true`. Additionally, you may provide a service and method name pair:

```yaml
                    persistence:
                        listener:
                            is_indexable_callback: [ "%custom_service_id%", "isIndexable" ]
```

In this case, the callback_class will be the `isIndexable()` method on the specified
service and the object being considered for indexing will be passed as the only
argument. This allows you to do more complex validation (e.g. ACL checks).

If you have the [Symfony ExpressionLanguage](https://github.com/symfony/expression-language)
component installed, you can use expressions to evaluate the callback:

```yaml
                    persistence:
                        listener:
                            is_indexable_callback: "user.isActive() && user.hasRole('ROLE_USER')"
```

As you might expect, new entities will only be indexed if the callback_class returns
`true`. Additionally, modified entities will be updated or removed from the
index depending on whether the callback_class returns `true` or `false`, respectively.
The delete listener disregards the callback_class.

> **Propel** doesn't support this feature yet.