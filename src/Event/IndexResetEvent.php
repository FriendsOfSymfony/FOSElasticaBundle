<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
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
class IndexResetEvent extends IndexEvent
{
    /**
     * @var bool
     */
    private $force;

    /**
     * @var bool
     */
    private $populating;

    public function __construct(string $index, bool $populating, bool $force)
    {
        parent::__construct($index);

        $this->force = $force;
        $this->populating = $populating;
    }

    public function isForce(): bool
    {
        return $this->force;
    }

    public function isPopulating(): bool
    {
        return $this->populating;
    }

    public function setForce(bool $force)
    {
        $this->force = $force;
    }
}
