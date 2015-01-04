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
 * Populate Event
 *
 * @author Oleg Andreyev <oleg.andreyev@intexsys.lv>
 */
class PopulateEvent extends Event
{
    const PRE_INDEX_POPULATE = 'elastica.index.index_pre_populate';
    const POST_INDEX_POPULATE = 'elastica.index.index_post_populate';

    const PRE_TYPE_POPULATE = 'elastica.index.type_pre_populate';
    const POST_TYPE_POPULATE = 'elastica.index.type_post_populate';

    /**
     * @var string
     */
    private $index;

    /**
     * @var string
     */
    private $type;

    /**
     * @var bool
     */
    private $reset;

    /**
     * @var array
     */
    private $options;

    /**
     * @param string      $index
     * @param string|null $type
     * @param boolean     $reset
     * @param array       $options
     */
    public function __construct($index, $type, $reset, $options)
    {
        $this->index   = $index;
        $this->type    = $type;
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
     * @return string
     */
    public function getType()
    {
        return $this->type;
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
}
