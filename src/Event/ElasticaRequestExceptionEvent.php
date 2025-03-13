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

use Elastica\Exception\ExceptionInterface;
use Elastica\Request;

class ElasticaRequestExceptionEvent
{
    private Request $request;
    private ExceptionInterface $exception;

    public function __construct(
        Request $request,
        ExceptionInterface $exception
    ) {
        $this->request = $request;
        $this->exception = $exception;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function getException(): ExceptionInterface
    {
        return $this->exception;
    }
}
