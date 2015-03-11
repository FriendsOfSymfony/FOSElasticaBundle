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
class TypePopulateEvent extends Event
{
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
     * @var array
     */
    private $options;

    /**
     * @param string $index
     * @param string $type
     * @param array $options
     */
    public function __construct($index, $type, $options)
    {
        $this->index   = $index;
        $this->type    = $type;
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
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    public function setOptions($options)
    {
        $this->options = $options;
    }
}
