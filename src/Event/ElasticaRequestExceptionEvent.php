<?php

declare(strict_types=1);

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <https://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Event;

use Elastic\Elasticsearch\Exception\ElasticsearchException;
use Psr\Http\Message\RequestInterface;

class ElasticaRequestExceptionEvent
{
    public function __construct(private readonly RequestInterface $request, private readonly ElasticsearchException $exception)
    {
    }

    public function getRequest(): RequestInterface
    {
        return $this->request;
    }

    public function getException(): ElasticsearchException
    {
        return $this->exception;
    }
}
