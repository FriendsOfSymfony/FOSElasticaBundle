<?php

namespace FOS\ElasticaBundle\Elastica;

use Elastica\Document;
use Elastica\Query;
use Elastica\Request;
use Elastica\Type;

class TransformingType extends Type
{
    /**
     * Overridden default method that returns our TransformingResultSet.
     *
     * {@inheritdoc}
     */
    public function moreLikeThis(Document $doc, $params = array(), $query = array())
    {
        $path = $doc->getId() . '/_mlt';
        $query = Query::create($query);
        $response = $this->request($path, Request::GET, $query->toArray(), $params);

        return new TransformingResultSet($response, $query, $this->_index->getClient()->getResultTransformer());
    }
}
