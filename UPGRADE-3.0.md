UPGRADE FROM 2.x to 3.0
=======================

### ElasticSearch Synchronization Event

 * Prior to 3.0, the ElasticSearch index was synchronized in the `postInsert`,
   `postUpdate`, and `pre/postRemove` events which fire before flush. Because
   of this, exceptions thrown when flushing would cause the data source and
   ElasticSearch index to fall out of sync.

   As of 3.0, ElasticSearch is updated `postFlush` by default.
