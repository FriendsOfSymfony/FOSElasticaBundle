<?php

namespace FOS\ElasticaBundle\Elastica;

use Elastica\Client;
use Elastica\Request;
use Elastica\Search;
use FOS\ElasticaBundle\Transformer\CombinedResultTransformer;

/**
 * Overridden Elastica methods to return our TransformingResultSet
 */
class TransformingSearch extends Search
{
    /**
     * Search in the set indices, types
     *
     * @param  mixed $query
     * @param  int|array $options OPTIONAL Limit or associative array of options (option=>value)
     * @throws \Elastica\Exception\InvalidException
     * @return TransformingResultSet
     */
    public function search($query = '', $options = null)
    {
        $this->setOptionsAndQuery($options, $query);

        $query = $this->getQuery();
        $path = $this->getPath();

        $params = $this->getOptions();

        // Send scroll_id via raw HTTP body to handle cases of very large (> 4kb) ids.
        if ('_search/scroll' == $path) {
            $data = $params[self::OPTION_SCROLL_ID];
            unset($params[self::OPTION_SCROLL_ID]);
        } else {
            $data = $query->toArray();
        }

        $response = $this->getClient()->request(
            $path,
            Request::GET,
            $data,
            $params
        );

        return new TransformingResultSet($response, $query, $this->_client->getResultTransformer());
    }

    /**
     *
     * @param mixed $query
     * @param $fullResult (default = false) By default only the total hit count is returned. If set to true, the full ResultSet including facets is returned.
     * @return int|TransformingResultSet
     */
    public function count($query = '', $fullResult = false)
    {
        $this->setOptionsAndQuery(null, $query);

        $query = $this->getQuery();
        $path = $this->getPath();

        $response = $this->getClient()->request(
            $path,
            Request::GET,
            $query->toArray(),
            array(self::OPTION_SEARCH_TYPE => self::OPTION_SEARCH_TYPE_COUNT)
        );
        $resultSet = new TransformingResultSet($response, $query, $this->_client->getResultTransformer());

        return $fullResult ? $resultSet : $resultSet->getTotalHits();
    }
}
