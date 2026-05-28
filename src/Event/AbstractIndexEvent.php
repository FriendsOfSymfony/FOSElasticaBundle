<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <https://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

abstract class AbstractIndexEvent extends Event
{
    public function __construct(private readonly string $index)
    {
    }

    public function getIndex(): string
    {
        return $this->index;
    }
}
