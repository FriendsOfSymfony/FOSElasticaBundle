UPGRADE FROM 3.2 to 4.0
=======================

### Faceted Searching

  * Facets have been removed in Elasticsearch 2.0. Please use aggregations instead.

  Before:
  ```php
       $query = new \Elastica\Query();
       $facet = new \Elastica\Facet\Terms('tags');
       $facet->setField('companyGroup');
       $query->addFacet($facet);

       $companies = $finder->findPaginated($query);
       $companies->setMaxPerPage($params['limit']);
       $companies->setCurrentPage($params['page']);

       $facets = $companies->getAdapter()->getFacets();
  ```

  After:
  ```php
       $query = new \Elastica\Query();
       $agg = new \Elastica\Aggregation\Terms('tags');
       $agg->setField('companyGroup');
       $query->addAggregation($agg);

       $companies = $finder->findPaginated($query);
       $companies->setMaxPerPage($params['limit']);
       $companies->setCurrentPage($params['page']);

       $aggs = $companies->getAdapter()->getAggregations();
  ```
