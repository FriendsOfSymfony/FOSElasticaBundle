<?php

namespace FOS\ElasticaBundle\Elastica;

use Elastica\Query;
use Elastica\Response;
use Elastica\ResultSet;
use FOS\ElasticaBundle\Transformer\CombinedResultTransformer;
use FOS\ElasticaBundle\Transformer\TransformerFactoryInterface;

class TransformingResultSet extends ResultSet
{
    /**
     * @var \FOS\ElasticaBundle\Transformer\CombinedResultTransformer
     */
    private $resultTransformer;

    /**
     * If a transformation has already been performed on this ResultSet or not.
     *
     * @var bool
     */
    private $transformed = false;

    public function __construct(Response $response, Query $query, CombinedResultTransformer $resultTransformer)
    {
        parent::__construct($response, $query);

        $this->resultTransformer = $resultTransformer;
    }

    /**
     * Overridden default method to set our TransformingResult objects.
     *
     * @param \Elastica\Response $response Response object
     */
    protected function _init(Response $response)
    {
        $this->_response = $response;
        $result = $response->getData();
        $this->_totalHits = isset($result['hits']['total']) ? $result['hits']['total'] : 0;
        $this->_maxScore = isset($result['hits']['max_score']) ? $result['hits']['max_score'] : 0;
        $this->_took = isset($result['took']) ? $result['took'] : 0;
        $this->_timedOut = !empty($result['timed_out']);
        if (isset($result['hits']['hits'])) {
            foreach ($result['hits']['hits'] as $hit) {
                $this->_results[] = new TransformingResult($hit, $this);
            }
        }
    }

    /**
     * Returns an array of transformed results.
     *
     * @return object[]
     */
    public function getTransformed()
    {
        $this->transform();

        return array_map(function (TransformingResult $result) {
            return $result->getTransformed();
        }, $this->getResults());
    }

    /**
     * Triggers the transformation of all Results.
     */
    public function transform()
    {
        if ($this->transformed) {
            return;
        }

        if (!$this->count()) {
            return;
        }

        $this->resultTransformer->transform($this->getResults());
        $this->transformed = true;
    }
}
