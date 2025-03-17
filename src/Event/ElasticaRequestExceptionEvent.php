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

use Elastic\Elasticsearch\Exception\ElasticsearchException;
use Psr\Http\Message\RequestInterface;

class ElasticaRequestExceptionEvent
{
    private RequestInterface $request;
    private ElasticsearchException $exception;

    public function __construct(
        RequestInterface $request,
        ElasticsearchException $exception
    ) {
        $this->request = $request;
        $this->exception = $exception;
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
