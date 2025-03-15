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

use Elastica\Request;
use Symfony\Contracts\EventDispatcher\Event;

class PreElasticaRequestEvent extends Event
{
    private string $path;
    private string $method;

    /**
     * @var array<string, mixed>|string
     */
    private $data;

    /**
     * @var array<string, mixed>
     */
    private array $query;
    private string $contentType;

    public function __construct(
        string $path,
        string $method,
        $data,
        array $query,
        string $contentType = Request::DEFAULT_CONTENT_TYPE
    ) {
        $this->path = $path;
        $this->method = $method;
        $this->data = $data;
        $this->query = $query;
        $this->contentType = $contentType;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @return array<string, mixed>|string
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return array<string, mixed>
     */
    public function getQuery(): array
    {
        return $this->query;
    }

    public function getContentType(): string
    {
        return $this->contentType;
    }
}
