<?php

namespace FOS\ElasticaBundle\Tests\Logger;

use FOS\ElasticaBundle\Logger\ElasticaLogger;

/**
 * @author Richard Miller <info@limethinking.co.uk>
 */
class ElasticaLoggerTest extends \PHPUnit_Framework_TestCase
{

    public function testGetZeroIfNoQueriesAdded()
    {
        $elasticaLogger = new ElasticaLogger;
        $this->assertEquals(0, $elasticaLogger->getNbQueries());
    }

    public function testCorrectAmountIfRandomNumberOfQueriesAdded()
    {
        $elasticaLogger = new ElasticaLogger(null, true);

        $total = rand(1, 15);
        for ($i = 0; $i < $total; $i++) {
            $elasticaLogger->logQuery('testPath', 'testMethod', array('data'), 12);
        }

        $this->assertEquals($total, $elasticaLogger->getNbQueries());
    }

    public function testCorrectlyFormattedQueryReturned()
    {
        $elasticaLogger = new ElasticaLogger(null, true);

        $path   = 'testPath';
        $method = 'testMethod';
        $data   = array('data');
        $time   = 12;

        $expected = array(
            'path'        => $path,
            'method'      => $method,
            'data'        => $data,
            'executionMS' => $time
        );

        $elasticaLogger->logQuery($path, $method, $data, $time);
        $returnedQueries = $elasticaLogger->getQueries();
        $this->assertEquals($expected, $returnedQueries[0]);
    }

    public function testNoQueriesStoredIfDebugFalseAdded()
    {
        $elasticaLogger = new ElasticaLogger(null, false);

        $total = rand(1, 15);
        for ($i = 0; $i < $total; $i++) {
            $elasticaLogger->logQuery('testPath', 'testMethod', array('data'), 12);
        }

        $this->assertEquals(0, $elasticaLogger->getNbQueries());
    }

    public function testQueryIsLogged()
    {
        $loggerMock = $this->getMockBuilder('Symfony\Component\HttpKernel\Log\LoggerInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $elasticaLogger = new ElasticaLogger($loggerMock);

        $path   = 'testPath';
        $method = 'testMethod';
        $data   = array('data');
        $time   = 12;

        $expectedMessage = 'testPath (testMethod) 12000.00 ms';

        $loggerMock->expects($this->once())
            ->method('info')
            ->with(
                $this->equalTo($expectedMessage),
                $this->equalTo($data)
            );

        $elasticaLogger->logQuery($path, $method, $data, $time);
    }

}
