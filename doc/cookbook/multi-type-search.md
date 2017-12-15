Multi type search
===============

If you want to limit your search to just few selected types in given index you could do that this way:

```yaml
fos_elastica:
    indexes:
        app:
            types:
                article:
                    properties:
                        title: ~
                        desc: ~
                    # ....
                news:
                    properties:
                        title: ~
                        desc: ~
                    # ....
```

```php
$query = 'search-string';
$mngr = $this->get('fos_elastica.index_manager');

$search = $mngr->getIndex('app')->createSearch();
$search->addType('article');
$search->addType('news');
$resultSet = $search->search($query);

$transformer = $this->get('fos_elastica.elastica_to_model_transformer.collection.app');
$results = $transformer->transform($resultSet->getResults());
```
