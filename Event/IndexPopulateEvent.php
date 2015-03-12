<?php

/**
 * This file is part of the FOSElasticaBundle project.
 *
 * (c) FriendsOfSymfony <https://github.com/FriendsOfSymfony/FOSElasticaBundle/graphs/contributors>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Index Populate Event.
 *
 * @author Oleg Andreyev <oleg.andreyev@intexsys.lv>
 */
class IndexPopulateEvent extends Event
{
    const PRE_INDEX_POPULATE = 'elastica.index.index_pre_populate';
    const POST_INDEX_POPULATE = 'elastica.index.index_post_populate';

    /**
     * @var string
     */
    private $index;

    /**
     * @var bool
     */
    private $reset;

    /**
     * @var array
     */
    private $options;

    /**
     * @param string  $index
     * @param boolean $reset
     * @param array   $options
     */
    public function __construct($index, $reset, $options)
    {
        $this->index   = $index;
        $this->reset   = $reset;
        $this->options = $options;
    }

    /**
     * @return string
     */
    public function getIndex()
    {
        return $this->index;
    }

    /**
     * @return boolean
     */
    public function isReset()
    {
        return $this->reset;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param boolean $reset
     */
    public function setReset($reset)
    {
        $this->reset = $reset;
    }
}
