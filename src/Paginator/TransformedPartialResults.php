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

use Elastica\ResultSet;
use FOS\ElasticaBundle\Transformer\ElasticaToModelTransformerInterface;

/**
 * Partial transformed result set.
 */
class TransformedPartialResults extends RawPartialResults
{
    protected $transformer;

    public function __construct(ResultSet $resultSet, ElasticaToModelTransformerInterface $transformer)
    {
        parent::__construct($resultSet);

        $this->transformer = $transformer;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return $this->transformer->transform($this->resultSet->getResults());
    }
}
