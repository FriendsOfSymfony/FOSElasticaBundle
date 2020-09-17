<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <https://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Message;

class AsyncPersistPage
{
    /**
     * @var int
     */
    private $page;

    /**
     * @var array
     */
    private $options;

    public function __construct(int $page, array $options)
    {
        $this->page = $page;
        $this->options = $options;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function getOptions(): array
    {
        return $this->options;
    }
}
