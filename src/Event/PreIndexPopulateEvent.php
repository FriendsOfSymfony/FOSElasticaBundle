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

final class PreIndexPopulateEvent extends AbstractIndexPopulateEvent
{
    /**
     * @param mixed $value
     */
    public function setOption(string $name, $value): self
    {
        $this->options[$name] = $value;

        return $this;
    }

    public function setReset(bool $reset): self
    {
        $this->reset = $reset;

        return $this;
    }
}
