<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <https://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Tests\Unit\Logger;

use FOS\ElasticaBundle\Logger\ElasticaLogger;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @author Richard Miller <info@limethinking.co.uk>
 *
 * @internal
 */
class ElasticaLoggerTest extends TestCase
{
    public function testGetZeroIfNoQueriesAdded()
    {
        $elasticaLogger = new ElasticaLogger();
        $this->assertSame(0, $elasticaLogger->getNbQueries());
    }

    public function testCorrectAmountIfRandomNumberOfQueriesAdded()
    {
        $elasticaLogger = new ElasticaLogger(null, true);

        $total = \rand(1, 15);
        for ($i = 0; $i < $total; ++$i) {
            $elasticaLogger->logQuery('testPath', 'testMethod', ['data'], 12);
        }

        $this->assertSame($total, $elasticaLogger->getNbQueries());
    }

    public function testCorrectlyFormattedQueryReturned()
    {
        $elasticaLogger = new ElasticaLogger(null, true);

        $path = 'testPath';
        $method = 'testMethod';
        $data = ['data'];
        $time = 12;
        $connection = ['host' => 'localhost', 'port' => '8999', 'transport' => 'https'];
        $query = ['search_type' => 'dfs_query_then_fetch'];

        $expected = [
            'path' => $path,
            'method' => $method,
            'data' => [$data],
            'executionMS' => $time * 1000,
            'engineMS' => 0,
            'connection' => $connection,
            'queryString' => $query,
            'itemCount' => 0,
        ];

        $elasticaLogger->logQuery($path, $method, $data, $time, $connection, $query);
        $returnedQueries = $elasticaLogger->getQueries();
        $this->assertArrayHasKey('backtrace', $returnedQueries[0]);
        $this->assertNotEmpty($returnedQueries[0]['backtrace']);
        unset($returnedQueries[0]['backtrace']);
        $this->assertSame($expected, $returnedQueries[0]);
    }

    public function testNoQueriesStoredIfDebugFalseAdded()
    {
        $elasticaLogger = new ElasticaLogger(null, false);

        $total = \rand(1, 15);
        for ($i = 0; $i < $total; ++$i) {
            $elasticaLogger->logQuery('testPath', 'testMethod', ['data'], 12);
        }

        $this->assertSame(0, $elasticaLogger->getNbQueries());
    }

    public function testQueryIsLogged()
    {
        $loggerMock = $this->getMockLogger();

        $elasticaLogger = new ElasticaLogger($loggerMock);

        $path = 'testPath';
        $method = 'testMethod';
        $data = ['data'];
        $time = 12;

        $expectedMessage = 'testPath (testMethod) 12000.00 ms';

        $loggerMock->expects($this->once())
            ->method('info')
            ->with(
                $this->equalTo($expectedMessage),
                $this->equalTo($data)
            )
        ;

        $elasticaLogger->logQuery($path, $method, $data, $time);
    }

    /**
     * @return array
     */
    public function logLevels()
    {
        return [
            ['emergency'],
            ['alert'],
            ['critical'],
            ['error'],
            ['warning'],
            ['notice'],
            ['info'],
            ['debug'],
        ];
    }

    /**
     * @dataProvider logLevels
     */
    public function testMessagesCanBeLoggedAtSpecificLogLevels($level)
    {
        $message = 'foo';
        $context = ['data'];

        $loggerMock = $this->getMockLoggerForLevelMessageAndContext($level, $message, $context);

        \call_user_func([$loggerMock, $level], $message, $context);
    }

    public function testMessagesCanBeLoggedToArbitraryLevels()
    {
        $loggerMock = $this->getMockLogger();

        $level = 'info';
        $message = 'foo';
        $context = ['data'];

        $loggerMock->expects($this->once())
            ->method('log')
            ->with(
                $level,
                $message,
                $context
            )
        ;

        $elasticaLogger = new ElasticaLogger($loggerMock);

        $elasticaLogger->log($level, $message, $context);
    }

    public function testQueryCanBeMultilineStrings()
    {
        $elasticaLogger = new ElasticaLogger(null, true);

        $data = "{\"foo\": \"bar\"}\n{\"foo\": \"baz\"}\n";
        $elasticaLogger->logQuery('path', 'method', $data, 0);
        $this->assertCount(2, $elasticaLogger->getQueries()[0]['data']);
        $this->assertEquals(['foo' => 'bar'], $elasticaLogger->getQueries()[0]['data'][0]);
    }

    public function testQueryCanBeAnArray()
    {
        $elasticaLogger = new ElasticaLogger(null, true);

        $data = ['foo' => 'bar'];
        $elasticaLogger->logQuery('path', 'method', $data, 0);
        $this->assertCount(1, $elasticaLogger->getQueries()[0]['data']);
        $this->assertEquals(['foo' => 'bar'], $elasticaLogger->getQueries()[0]['data'][0]);
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|LoggerInterface
     */
    private function getMockLogger()
    {
        return $this->createMock(LoggerInterface::class);
    }

    /**
     * @param string $level
     * @param string $message
     * @param array  $context
     *
     * @return ElasticaLogger
     */
    private function getMockLoggerForLevelMessageAndContext($level, $message, $context)
    {
        $loggerMock = $this->createMock(LoggerInterface::class);

        $loggerMock->expects($this->once())
            ->method('log')
            ->with(
                $level,
                $this->equalTo($message),
                $this->equalTo($context)
            )
        ;

        return new ElasticaLogger($loggerMock);
    }
}
