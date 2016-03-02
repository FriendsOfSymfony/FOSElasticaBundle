Hints on result hydration
===============

When using Doctrine as your persistance driver, You may configure FOSElasticaBundle to use
[query hints](http://doctrine-orm.readthedocs.org/en/latest/reference/dql-doctrine-query-language.html#query-hints) when hydrating your objects.

To configure FOSElasticaBundle to use hints when hydrating objects, add an entry 
to the `hints` array of `elastica_to_model_transformer` configuration section.  
Each entry must contain a `name` of the hint and a `value` to use.

```yaml
fos_elastica:
    indexes:
        website:
            types:
                user:
                    persistence:
                        elastica_to_model_transformer:
                            hints:
                                - {name: 'doctrine.customOutputWalker', value: 'Gedmo\Translatable\Query\TreeWalker\TranslationWalker'}
```

This is especially useful if You're using features that require additional information when hydrating an object
(like the translatable behavior from [Doctrine2 behavioral extensions](https://github.com/Atlantic18/DoctrineExtensions)) and You don't want
Doctrine to issue additional queries to retrieve it.

This option is only available for the orm driver.
