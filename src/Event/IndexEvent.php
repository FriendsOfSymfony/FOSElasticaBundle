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


use Symfony\Contracts\EventDispatcher\Event;

class IndexEvent extends Event
{
    /**
     * @var string
     */
    private $index;

    /**
     * @param string $index
     */
    public function __construct($index)
    {
        $this->index = $index;
    }

    /**
     * @return string
     */
    public function getIndex()
    {
        return $this->index;
    }
}
