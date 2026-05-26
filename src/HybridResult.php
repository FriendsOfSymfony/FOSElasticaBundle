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

/**
 * @template T of object
 */
class HybridResult
{
    /**
     * @var Result
     */
    protected $result;

    /**
     * @param T|null $transformed
     */
    public function __construct(Result $result, protected $transformed = null)
    {
        $this->result = $result;
    }

    /**
     * @return T|null
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
