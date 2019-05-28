Aliased Indexes
===============

You can set up FOSElasticaBundle to use aliases for indexes which allows you to run an
index population without resetting the index currently being used by the application.

> *Note*: When you're using an alias, resetting an individual type will still cause a
> reset for that type.

To configure FOSElasticaBundle to use aliases for an index, set the use_alias option to
true.

```yaml
fos_elastica:
    indexes:
        app:
            use_alias: true
```

The process for setting up aliases on an existing application is slightly more complicated
because the bundle is not able to set an alias as the same name as an index. You have 2
options on how to handle this:

1) Option with downtime : Delete the index from Elasticsearch. This option will make searching unavailable in your
   application until a population has completed itself, and an alias is created.
   
```bash
$ curl -XDELETE 'http://localhost:9200/app/'
```

2) Option without downtime : Change the index_name parameter for your index to something new, and manually alias the
   current index to the new index_name, which will then be replaced when you run a repopulate.

```bash
# before actions
curl -XGET 'http://localhost:9200/_alias/?pretty'
{
  "app" : {
    "aliases" : { }
  }
}

# create alias to switch after with no downtime
$ curl -XPOST 'http://localhost:9200/_aliases' -H 'Content-Type: application/json'  -d '
{
    "actions" : [
        { "add" : { "index" : "app", "alias" : "app_prod" } }
    ]
}'

#check alias is ok
curl -XGET 'http://localhost:9200/_alias/?pretty'
{
  "app" : {
    "aliases" : { "app_prod" : { } }
  }
}

```

```yaml
#index name is alias name
fos_elastica:
    indexes:
        app:
            use_alias: true
            index_name: app_prod
```

clear caches etc, now fos use alias instead of index
```bash
bin/console -eprod 'fos:elastica:populate'
```
in other cli in // check indexes during populate process
```bash
curl -XGET 'http://localhost:9200/_alias/?pretty'
{
  "app" : {
    "aliases" : {
      "app_prod" : { }
    }
  },
  "app_prod_2019-05-28-153852" : {
    "aliases" : { }
  }
}

```
when 'fos:elastica:populate' command finish

```bash
curl -XGET 'http://localhost:9200/_alias/?pretty'
{
  "app_prod_2019-05-28-153852" : {
    "aliases" : {
        "app_prod" : { }
    }
  }
}

```
