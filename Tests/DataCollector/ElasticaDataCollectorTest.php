<?php

namespace FOS\ElasticaBundle\Tests\DataCollector;

use FOS\ElasticaBundle\DataCollector\ElasticaDataCollector;

/**
 * @author Richard Miller <info@limethinking.co.uk>
 */
class ElasticaDataCollectorTest extends \PHPUnit_Framework_TestCase
{
    public function testCorrectAmountOfQueries()
    {
        /** @var $requestMock \PHPUnit_Framework_MockObject_MockObject|\Symfony\Component\HttpFoundation\Request */
        $requestMock = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()
            ->getMock();

        /** @var $responseMock \PHPUnit_Framework_MockObject_MockObject|\Symfony\Component\HttpFoundation\Response */
        $responseMock = $this->getMockBuilder('Symfony\Component\HttpFoundation\Response')
            ->disableOriginalConstructor()
            ->getMock();

        /** @var $loggerMock \PHPUnit_Framework_MockObject_MockObject|\FOS\ElasticaBundle\Logger\ElasticaLogger */
        $loggerMock = $this->getMockBuilder('FOS\ElasticaBundle\Logger\ElasticaLogger')
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
        /** @var $requestMock \PHPUnit_Framework_MockObject_MockObject|\Symfony\Component\HttpFoundation\Request */
        $requestMock = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()
            ->getMock();

        /** @var $responseMock \PHPUnit_Framework_MockObject_MockObject|\Symfony\Component\HttpFoundation\Response */
        $responseMock = $this->getMockBuilder('Symfony\Component\HttpFoundation\Response')
            ->disableOriginalConstructor()
            ->getMock();

        /** @var $loggerMock \PHPUnit_Framework_MockObject_MockObject|\FOS\ElasticaBundle\Logger\ElasticaLogger */
        $loggerMock = $this->getMockBuilder('FOS\ElasticaBundle\Logger\ElasticaLogger')
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
        /** @var $requestMock \PHPUnit_Framework_MockObject_MockObject|\Symfony\Component\HttpFoundation\Request */
        $requestMock = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()
            ->getMock();

        /** @var $responseMock \PHPUnit_Framework_MockObject_MockObject|\Symfony\Component\HttpFoundation\Response */
        $responseMock = $this->getMockBuilder('Symfony\Component\HttpFoundation\Response')
            ->disableOriginalConstructor()
            ->getMock();

        /** @var $loggerMock \PHPUnit_Framework_MockObject_MockObject|\FOS\ElasticaBundle\Logger\ElasticaLogger */
        $loggerMock = $this->getMockBuilder('FOS\ElasticaBundle\Logger\ElasticaLogger')
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

    public function testName()
    {
        $loggerMock = $this->getMockBuilder('FOS\ElasticaBundle\Logger\ElasticaLogger')
            ->disableOriginalConstructor()
            ->getMock();

        $elasticaDataCollector = new ElasticaDataCollector($loggerMock);

        $this->assertEquals('elastica', $elasticaDataCollector->getName());
    }
}
