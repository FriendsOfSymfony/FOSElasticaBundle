<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle;

use Elastica\Result;

class HybridResult
{
    protected $result;
    protected $transformed;

    public function __construct(Result $result, $transformed = null)
    {
        $this->result = $result;
        $this->transformed = $transformed;
    }

    public function getTransformed()
    {
        return $this->transformed;
    }

    public function getResult(): Result
    {
        return $this->result;
    }
}
