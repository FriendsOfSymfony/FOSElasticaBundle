<?php

namespace FOS\ElasticaBundle\Elastica;

use Elastica\Result;

class TransformingResult extends Result
{
    /**
     * The transformed hit.
     *
     * @var mixed
     */
    private $transformed;

    /**
     * @var TransformingResultSet
     */
    private $resultSet;

    public function __construct(array $hit, TransformingResultSet $resultSet)
    {
        parent::__construct($hit);

        $this->resultSet = $resultSet;
    }

    /**
     * Returns the transformed result of the hit.
     *
     * @return mixed
     */
    public function getTransformed()
    {
        if (null === $this->transformed) {
            $this->resultSet->transform();
        }

        return $this->transformed;
    }

    /**
     * An internal method used to set the transformed result on the Result.
     *
     * @internal
     */
    public function setTransformed($transformed)
    {
        $this->transformed = $transformed;
    }
}
