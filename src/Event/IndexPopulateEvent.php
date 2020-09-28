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
 * Index Populate Event.
 *
 * @author Oleg Andreyev <oleg.andreyev@intexsys.lv>
 */
class IndexPopulateEvent extends IndexEvent
{
    /**
     * @var bool
     */
    private $reset;

    /**
     * @var array
     */
    private $options;

    public function __construct(string $index, bool $reset, array $options)
    {
        parent::__construct($index);

        $this->reset = $reset;
        $this->options = $options;
    }

    public function isReset(): bool
    {
        return $this->reset;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function setReset(bool $reset)
    {
        $this->reset = $reset;
    }

    /**
     * @return mixed
     *
     * @throws \InvalidArgumentException if option does not exist
     */
    public function getOption(string $name)
    {
        if (!isset($this->options[$name])) {
            throw new \InvalidArgumentException(\sprintf('The "%s" option does not exist.', $name));
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
