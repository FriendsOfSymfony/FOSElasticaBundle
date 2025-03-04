Index templates
================

Index templates allow you to define templates that will automatically be applied when new indices are created 
(see more in [official documentation](https://www.elastic.co/guide/en/elasticsearch/reference/6.6/indices-templates.html)).
Index Templates widely is used to create historically indexes:

* storing logs (Kibana) to prevent index growing because of incorrectly mapped fields
* metrics (Marvel) to archive or delete old information

Here's example on how to configure index templates:

```yaml
# app/config/config.yml

fos_elastica:
    index_templates:
        <name>:
            client: default
            template_name: <template name>
            index_patterns: ["some_index_*", "some_other_index_*"]
            settings:
                number_of_shards: 1
                number_of_replicas: 0
            types:
                auto_suggest:
                    mappings:
                        <field name>:  <params>
                         ...
```

Index template is similar to index configuration and has the same fields like `settings`, `client`, etc. with additional fields:

1. `template_name` - template name. If omitted then used key (`<name>`) of `index_templates` section. Example: `template_1`
2. `index_patterns` - index pattern(s) on which to apply the template. Example: `te*` or `bar*`

To apply templates changes, you should run `fos:elastica:reset-templates` command:

* `--index` - index template name to reset. If no index template name specified than all templates will be reset
* `--force-delete` - will delete all indexes that match index templates patterns. Aware that pattern may match various indexes. Note that in order to use this feature in Elasticsearch 8+ you *MUST* set the configuration option `action.destructive_requires_name` to `false`.

You must run the following command to sync templates configuration on ES server with YAML configurations:
```bash
php bin/console fos:elastica:reset-templates
```

You can build-in this command into the deployment process to automate template configuration sync.
