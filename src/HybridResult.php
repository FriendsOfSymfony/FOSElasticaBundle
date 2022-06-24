<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <https://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle;

use Elastica\Result;

class HybridResult
{
    /**
     * @var Result
     */
    protected $result;
    /**
     * @var ?object
     */
    protected $transformed;

    /**
     * @param ?object $transformed
     */
    public function __construct(Result $result, $transformed = null)
    {
        $this->result = $result;
        $this->transformed = $transformed;
    }

    /**
     * @return ?object
     */
    public function getTransformed()
    {
        return $this->transformed;
    }

    public function getResult(): Result
    {
        return $this->result;
    }
}
