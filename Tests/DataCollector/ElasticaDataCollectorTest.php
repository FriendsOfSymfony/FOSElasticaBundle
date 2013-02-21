<?php

namespace FOQ\ElasticaBundle\Tests\DataCollector;

use FOQ\ElasticaBundle\DataCollector\ElasticaDataCollector;

/**
 * @author Richard Miller <info@limethinking.co.uk>
 */
class ElasticaDataCollectorTest extends \PHPUnit_Framework_TestCase
{

    public function testCorrectAmountOfQueries()
    {
        $requestMock = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()
            ->getMock();

        $responseMock = $this->getMockBuilder('Symfony\Component\HttpFoundation\Response')
            ->disableOriginalConstructor()
            ->getMock();

        $loggerMock = $this->getMockBuilder('FOQ\ElasticaBundle\Logger\ElasticaLogger')
            ->disableOriginalConstructor()
            ->getMock();

        $totalQueries = rand();

        $loggerMock->expects($this->once())
            ->method('getNbQueries')
            ->will($this->returnValue($totalQueries));

        $elasticaDataCollector = new ElasticaDataCollector($loggerMock);
        $elasticaDataCollector->collect($requestMock, $responseMock);
        $this->assertEquals($totalQueries, $elasticaDataCollector->getQueryCount());
    }

    public function testCorrectQueriesReturned()
    {
        $requestMock = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()
            ->getMock();

        $responseMock = $this->getMockBuilder('Symfony\Component\HttpFoundation\Response')
            ->disableOriginalConstructor()
            ->getMock();

        $loggerMock = $this->getMockBuilder('FOQ\ElasticaBundle\Logger\ElasticaLogger')
            ->disableOriginalConstructor()
            ->getMock();

        $queries = array('testQueries');

        $loggerMock->expects($this->once())
            ->method('getQueries')
            ->will($this->returnValue($queries));

        $elasticaDataCollector = new ElasticaDataCollector($loggerMock);
        $elasticaDataCollector->collect($requestMock, $responseMock);
        $this->assertEquals($queries, $elasticaDataCollector->getQueries());
    }

    public function testCorrectQueriesTime()
    {
        $requestMock = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()
            ->getMock();

        $responseMock = $this->getMockBuilder('Symfony\Component\HttpFoundation\Response')
            ->disableOriginalConstructor()
            ->getMock();

        $loggerMock = $this->getMockBuilder('FOQ\ElasticaBundle\Logger\ElasticaLogger')
            ->disableOriginalConstructor()
            ->getMock();

        $queries = array(array('executionMS' => 10), array('executionMS' => 20));

        $loggerMock->expects($this->once())
            ->method('getQueries')
            ->will($this->returnValue($queries));

        $elasticaDataCollector = new ElasticaDataCollector($loggerMock);
        $elasticaDataCollector->collect($requestMock, $responseMock);
        $this->assertEquals(30, $elasticaDataCollector->getTime());
    }

}
