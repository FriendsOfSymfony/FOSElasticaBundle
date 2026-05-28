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

/**
 * Index ResetEvent.
 *
 * @author Oleg Andreyev <oleg.andreyev@intexsys.lv>
 */
abstract class AbstractIndexResetEvent extends AbstractIndexEvent
{
    public function __construct(string $index, private readonly bool $populating, protected bool $force)
    {
        parent::__construct($index);
    }

    public function isForce(): bool
    {
        return $this->force;
    }

    public function isPopulating(): bool
    {
        return $this->populating;
    }
}
