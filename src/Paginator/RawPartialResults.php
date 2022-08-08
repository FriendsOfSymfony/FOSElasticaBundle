<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <https://friendsofsymfony.github.com/>
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
    /**
     * @var ResultSet
     */
    protected $resultSet;

    public function __construct(ResultSet $resultSet)
    {
        $this->resultSet = $resultSet;
    }

    /**
     * {@inheritdoc}
     *
     * @return list<array<string, mixed>>
     */
    public function toArray(): array
    {
        return \array_map(static function (Result $result) {
            return $result->getSource();
        }, $this->resultSet->getResults());
    }

    /**
     * {@inheritdoc}
     */
    public function getTotalHits(): int
    {
        return $this->resultSet->getTotalHits();
    }

    /**
     * {@inheritdoc}
     */
    public function getAggregations(): array
    {
        return $this->resultSet->getAggregations();
    }

    /**
     * @return array<string, mixed>
     */
    public function getSuggests(): array
    {
        return $this->resultSet->getSuggests();
    }
}
