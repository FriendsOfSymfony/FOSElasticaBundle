<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Tests\Unit\Event;

use FOS\ElasticaBundle\Exception\InvalidArgumentTypeException;
use PHPUnit\Framework\TestCase;

class InvalidArgumentTypeExceptionTest extends TestCase
{
    public function testConstruct()
    {
        $exception = new InvalidArgumentTypeException('value', 'expectedType');
    }
}
