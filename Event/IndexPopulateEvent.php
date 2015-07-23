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

/**
 * Index Populate Event.
 *
 * @author Oleg Andreyev <oleg.andreyev@intexsys.lv>
 */
class IndexPopulateEvent extends IndexEvent
{
    const PRE_INDEX_POPULATE = 'elastica.index.index_pre_populate';
    const POST_INDEX_POPULATE = 'elastica.index.index_post_populate';

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
        parent::__construct($index);

        $this->reset   = $reset;
        $this->options = $options;
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
