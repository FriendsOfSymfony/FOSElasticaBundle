<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Tests\DataCollector;

use FOS\ElasticaBundle\DataCollector\ElasticaDataCollector;
use FOS\ElasticaBundle\Logger\ElasticaLogger;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Richard Miller <info@limethinking.co.uk>
 */
class ElasticaDataCollectorTest extends TestCase
{
    public function testCorrectAmountOfQueries()
    {
        /** @var $requestMock \PHPUnit_Framework_MockObject_MockObject|Request */
        $requestMock = $this->createMock(Request::class);

        /** @var $responseMock \PHPUnit_Framework_MockObject_MockObject|Response */
        $responseMock = $this->createMock(Response::class);

        /** @var $loggerMock \PHPUnit_Framework_MockObject_MockObject|ElasticaLogger */
        $loggerMock = $this->createMock(ElasticaLogger::class);

        $totalQueries = rand();

        $loggerMock->expects($this->once())
            ->method('getNbQueries')
            ->will($this->returnValue($totalQueries));

        $elasticaDataCollector = new ElasticaDataCollector($loggerMock);
        $elasticaDataCollector->collect($requestMock, $responseMock);
        $this->assertSame($totalQueries, $elasticaDataCollector->getQueryCount());
    }

    public function testCorrectQueriesReturned()
    {
        /** @var $requestMock \PHPUnit_Framework_MockObject_MockObject|Request */
        $requestMock = $this->createMock(Request::class);

        /** @var $responseMock \PHPUnit_Framework_MockObject_MockObject|Response */
        $responseMock = $this->createMock(Response::class);

        /** @var $loggerMock \PHPUnit_Framework_MockObject_MockObject|ElasticaLogger */
        $loggerMock = $this->createMock(ElasticaLogger::class);

        $queries = ['testQueries'];

        $loggerMock->expects($this->once())
            ->method('getQueries')
            ->will($this->returnValue($queries));

        $elasticaDataCollector = new ElasticaDataCollector($loggerMock);
        $elasticaDataCollector->collect($requestMock, $responseMock);
        $this->assertSame($queries, $elasticaDataCollector->getQueries());
    }

    public function testCorrectQueriesTime()
    {
        /** @var $requestMock \PHPUnit_Framework_MockObject_MockObject|Request */
        $requestMock = $this->createMock(Request::class);

        /** @var $responseMock \PHPUnit_Framework_MockObject_MockObject|Response */
        $responseMock = $this->createMock(Response::class);

        /** @var $loggerMock \PHPUnit_Framework_MockObject_MockObject|ElasticaLogger */
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
            ->will($this->returnValue($queries));

        $elasticaDataCollector = new ElasticaDataCollector($loggerMock);
        $elasticaDataCollector->collect($requestMock, $responseMock);
        $this->assertSame(40, $elasticaDataCollector->getTime());
    }

    public function testName()
    {
        /** @var $loggerMock \PHPUnit_Framework_MockObject_MockObject|ElasticaLogger */
        $loggerMock = $this->createMock(ElasticaLogger::class);

        $elasticaDataCollector = new ElasticaDataCollector($loggerMock);

        $this->assertSame('elastica', $elasticaDataCollector->getName());
    }
}
