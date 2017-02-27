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
    /**
     * @Event("FOS\ElasticaBundle\Event\IndexPopulateEvent")
     */
    const PRE_INDEX_POPULATE = 'elastica.index.index_pre_populate';

    /**
     * @Event("FOS\ElasticaBundle\Event\IndexPopulateEvent")
     */
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

    /**
     * @param string $name
     *
     * @return mixed
     *
     * @throws \InvalidArgumentException if option does not exist
     */
    public function getOption($name)
    {
        if (!isset($this->options[$name])) {
            throw new \InvalidArgumentException(sprintf('The "%s" option does not exist.', $name));
        }

        return $this->options[$name];
    }

    /**
     * @param string $name
     * @param mixed  $value
     */
    public function setOption($name, $value)
    {
        $this->options[$name] = $value;
    }
}
