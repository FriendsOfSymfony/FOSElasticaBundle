<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Paginator;

use Elastica\Result;
use Elastica\ResultSet;

/**
 * Raw partial results transforms to a simple array.
 */
class RawPartialResults implements PartialResultsInterface
{
    protected $resultSet;

    /**
     * @param ResultSet $resultSet
     */
    public function __construct(ResultSet $resultSet)
    {
        $this->resultSet = $resultSet;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return array_map(function (Result $result) {
            return $result->getSource();
        }, $this->resultSet->getResults());
    }

    /**
     * {@inheritdoc}
     */
    public function getTotalHits()
    {
        return $this->resultSet->getTotalHits();
    }

    /**
     * {@inheritdoc}
     */
    public function getAggregations()
    {
        if ($this->resultSet->hasAggregations()) {
            return $this->resultSet->getAggregations();
        }

        return;
    }

    /**
     * {@inheritdoc}
     */
    public function getSuggests()
    {
        if ($this->resultSet->hasSuggests()) {
            return $this->resultSet->getSuggests();
        }

        return;
    }
}
