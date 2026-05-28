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
     * @param T|null $transformed
     */
    public function __construct(protected Result $result, protected ?object $transformed = null)
    {
    }

    /**
     * @return T|null
     */
    public function getTransformed(): ?object
    {
        return $this->transformed;
    }

    public function getResult(): Result
    {
        return $this->result;
    }
}
