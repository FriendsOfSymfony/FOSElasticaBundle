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

use Symfony\Component\EventDispatcher\Event as LegacyEvent;
use Symfony\Contracts\EventDispatcher\Event;

if (!class_exists(Event::class)) {
    /**
     * Symfony 3.4
     */

    /**
     * Type ResetEvent.
     *
     * @author Oleg Andreyev <oleg.andreyev@intexsys.lv>
     */
    class TypeResetEvent extends IndexEvent
    {
        /**
         * @LegacyEvent("FOS\ElasticaBundle\Event\TypeResetEvent")
         */
        const PRE_TYPE_RESET = 'elastica.index.type_pre_reset';
        /**
         * @LegacyEvent("FOS\ElasticaBundle\Event\TypeResetEvent")
         */
        const POST_TYPE_RESET = 'elastica.index.type_post_reset';
        /**
         * @var string
         */
        private $type;

        /**
         * @param string $index
         * @param string $type
         */
        public function __construct($index, $type)
        {
            parent::__construct($index);

            $this->type = $type;
        }

        /**
         * @return string
         */
        public function getType()
        {
            return $this->type;
        }
    }
} else {
    /**
     * Symfony >= 4.3
     */

    /**
     * Type ResetEvent.
     *
     * @author Oleg Andreyev <oleg.andreyev@intexsys.lv>
     */
    class TypeResetEvent extends IndexEvent
    {
        /**
         * @Event("FOS\ElasticaBundle\Event\TypeResetEvent")
         */
        const PRE_TYPE_RESET = 'elastica.index.type_pre_reset';
        /**
         * @Event("FOS\ElasticaBundle\Event\TypeResetEvent")
         */
        const POST_TYPE_RESET = 'elastica.index.type_post_reset';
        /**
         * @var string
         */
        private $type;

        /**
         * @param string $index
         * @param string $type
         */
        public function __construct($index, $type)
        {
            parent::__construct($index);

            $this->type = $type;
        }

        /**
         * @return string
         */
        public function getType()
        {
            return $this->type;
        }
    }
}
