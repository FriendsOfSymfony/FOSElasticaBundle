<?php

namespace FOS\ElasticaBundle\Index;


use Elastica\Bulk;
use Elastica\Exception\Bulk\ResponseException;
use FOS\ElasticaBundle\Elastica\Client;
use FOS\ElasticaBundle\Elastica\Index;

class Reindexer {

    const DEFAULT_BATCH_SIZE = 100;

    /**
     * Copies documents between indices, preserving their version
     *
     * @param Index $oldIndex
     * @param Index $newIndex
     * @param null|closure $loggerClosure
     * @param array $options
     * @param null|array $query
     * @return int
     */
    public function copyDocuments(Index $oldIndex, Index $newIndex, $loggerClosure = null, $options = array(), $query = null)
    {
        /** @var Client $client */
        $client = $oldIndex->getClient();

        $response = $client->request(
            $oldIndex->getName().'/_search?search_type=scan&scroll=1m',
            'GET',
            array(
                'size' => isset($options['batch-size']) ? $options['batch-size'] : self::DEFAULT_BATCH_SIZE,
                'version' => true,
                'query' => $query ? : array('match_all' => array())
                )
        );

        $errorCount = 0;

        do {
            $response = $client->request(
                '_search/scroll?scroll=1m&scroll_id='.$response->getScrollId(),
                'POST'
            );

            $data = $response->getData();
            $hitData = $data['hits'];

            $hitCount = count($hitData['hits']);

            if ($hitCount) {
                $bulk = new Bulk($client);
                $bulk->setIndex($newIndex);
                foreach ($hitData['hits'] as $hit) {
                    $bulk->addRawData(
                        array(
                            array('index' => array('_type' => $hit['_type'], '_id' => $hit['_id'], '_version' => $hit['_version'], '_version_type' => 'external')),
                            $hit['_source']
                        )
                    );
                }

                try {
                    $bulk->send();
                } catch (ResponseException $e) {
                    if (isset($options['ignore-errors']) && $options['ignore-errors']) {
                        $actionExceptions = $e->getActionExceptions();
                        $errorCount = count($actionExceptions);
                    } else {
                        throw $e;
                    }
                }
                if ($loggerClosure) {
                    $loggerClosure($hitCount, $hitData['total']);
                }
            }

        } while ($hitCount);

        return $errorCount;
    }
}
