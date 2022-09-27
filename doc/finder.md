# Finder

The service that enables you to fetch objects from Elasticsearch is called `Finder`.
You can create a custom Finder by implementing the `FinderInterface` interface and map it in config or via Symfony a tag.

Most projects require pagination. To enable paginationt - implement the `PaginatedFinderInterface`.

## Via Symfony tags
```yaml
# config/services.yaml
App\Finder\UserFinder:
    class: App\Finder\UserFinder
    tags:
        - { name: fos_elastica.finder, index: user }
```


## Via config yaml file

```yaml
# config/services.yaml
App\Finder\UserFinder:
    class: App\Finder\UserFinder
```

```yaml
# config/packages/fos_elastica.yaml
fos_elastica:
    indexes:
        user: # Index Name
            persistence:
                finder:
                    service: App\Finder\UserFinder

```
