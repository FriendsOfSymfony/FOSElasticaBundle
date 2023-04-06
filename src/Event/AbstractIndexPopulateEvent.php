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
 *
 * @phpstan-type TOptions = array{
 *     delete: bool,
 *     reset: bool,
 *     ignore_errors: bool,
 *     sleep: int,
 *     first_page: int,
 *     max_per_page: int,
 *     last_page?: int,
 *     indexName?: string,
 *     pager_persister: string
 * }
 */
abstract class AbstractIndexPopulateEvent extends AbstractIndexEvent
{
    /**
     * @var bool
     */
    protected $reset;

    /**
     * @var array
     * @phpstan-var TOptions
     */
    protected $options;

    /**
     * @phpstan-param TOptions $options
     */
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

    /**
     * @phpstan-return TOptions
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @throws \InvalidArgumentException if option does not exist
     *
     * @return mixed
     */
    public function getOption(string $name)
    {
        if (!isset($this->options[$name])) {
            throw new \InvalidArgumentException(\sprintf('The "%s" option does not exist.', $name));
        }

        return $this->options[$name];
    }
}
