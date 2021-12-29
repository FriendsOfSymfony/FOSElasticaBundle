<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <https://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Tests\Unit\DataCollector;

use FOS\ElasticaBundle\DataCollector\ElasticaDataCollector;
use FOS\ElasticaBundle\Logger\ElasticaLogger;
use FOS\ElasticaBundle\Tests\Unit\UnitTestHelper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Richard Miller <info@limethinking.co.uk>
 *
 * @internal
 */
class ElasticaDataCollectorTest extends UnitTestHelper
{
    public function testCorrectAmountOfQueries()
    {
        /** @var \PHPUnit\Framework\MockObject\MockObject|Request $requestMock */
        $requestMock = $this->createMock(Request::class);

        /** @var \PHPUnit\Framework\MockObject\MockObject|Response $responseMock */
        $responseMock = $this->createMock(Response::class);

        /** @var \PHPUnit\Framework\MockObject\MockObject|ElasticaLogger $loggerMock */
        $loggerMock = $this->createMock(ElasticaLogger::class);

        $totalQueries = \rand();

        $loggerMock->expects($this->once())
            ->method('getNbQueries')
            ->will($this->returnValue($totalQueries))
        ;

        $elasticaDataCollector = new ElasticaDataCollector($loggerMock);
        $elasticaDataCollector->collect($requestMock, $responseMock);
        $this->assertSame($totalQueries, $elasticaDataCollector->getQueryCount());
    }

    public function testCorrectQueriesReturned()
    {
        /** @var \PHPUnit\Framework\MockObject\MockObject|Request $requestMock */
        $requestMock = $this->createMock(Request::class);

        /** @var \PHPUnit\Framework\MockObject\MockObject|Response $responseMock */
        $responseMock = $this->createMock(Response::class);

        /** @var \PHPUnit\Framework\MockObject\MockObject|ElasticaLogger $loggerMock */
        $loggerMock = $this->createMock(ElasticaLogger::class);

        $queries = ['testQueries'];

        $loggerMock->expects($this->once())
            ->method('getQueries')
            ->will($this->returnValue($queries))
        ;

        $elasticaDataCollector = new ElasticaDataCollector($loggerMock);
        $elasticaDataCollector->collect($requestMock, $responseMock);
        $this->assertSame($queries, $elasticaDataCollector->getQueries());
    }

    public function testCorrectQueriesTime()
    {
        /** @var \PHPUnit\Framework\MockObject\MockObject|Request $requestMock */
        $requestMock = $this->createMock(Request::class);

        /** @var \PHPUnit\Framework\MockObject\MockObject|Response $responseMock */
        $responseMock = $this->createMock(Response::class);

        /** @var \PHPUnit\Framework\MockObject\MockObject|ElasticaLogger $loggerMock */
        $loggerMock = $this->createMock(ElasticaLogger::class);

        $queries = [[
            'engineMS' => 15,
            'executionMS' => 10,
        ], [
            'engineMS' => 25,
            'executionMS' => 20,
        ]];

        $loggerMock->expects($this->once())
            ->method('getQueries')
            ->will($this->returnValue($queries))
        ;

        $elasticaDataCollector = new ElasticaDataCollector($loggerMock);
        $elasticaDataCollector->collect($requestMock, $responseMock);
        $this->assertSame(40, $elasticaDataCollector->getTime());
    }

    public function testName()
    {
        /** @var \PHPUnit\Framework\MockObject\MockObject|ElasticaLogger $loggerMock */
        $loggerMock = $this->createMock(ElasticaLogger::class);

        $elasticaDataCollector = new ElasticaDataCollector($loggerMock);

        $this->assertSame('elastica', $elasticaDataCollector->getName());
    }

    public function testReset()
    {
        /** @var \PHPUnit\Framework\MockObject\MockObject|ElasticaLogger $loggerMock */
        $loggerMock = $this->createMock(ElasticaLogger::class);
        $loggerMock->expects($this->once())
            ->method('reset')
        ;

        $elasticaDataCollector = new ElasticaDataCollector($loggerMock);
        $elasticaDataCollector->reset();
        $this->assertSame([], $this->getProtectedProperty($elasticaDataCollector, 'data'));
    }
}
