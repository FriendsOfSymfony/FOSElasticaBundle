<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * This file is part of the FOSElasticaBundle project.
 *
 * (c) Tim Nagel <tim@nagel.com.au>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Tests\Unit\Index;

use Elastica\Client;
use Elastica\Exception\ResponseException;
use Elastica\Request;
use Elastica\Response;
use FOS\ElasticaBundle\Configuration\IndexConfig;
use FOS\ElasticaBundle\Elastica\Index;
use FOS\ElasticaBundle\Exception\AliasIsIndexException;
use FOS\ElasticaBundle\Index\AliasProcessor;
use PHPUnit\Framework\TestCase;

class AliasProcessorTest extends TestCase
{
    /**
     * @var AliasProcessor
     */
    private $processor;

    protected function setUp(): void
    {
        $this->processor = new AliasProcessor();
    }

    /**
     * @dataProvider getSetRootNameData
     *
     * @param array  $configArray
     * @param string $resultStartsWith
     */
    public function testSetRootName($configArray, $resultStartsWith)
    {
        $indexConfig = new IndexConfig($configArray);
        $index = $this->createMock(Index::class);
        $index->expects($this->once())
            ->method('overrideName')
            ->with($this->stringStartsWith($resultStartsWith));

        $this->processor->setRootName($indexConfig, $index);
    }

    public function testSwitchAliasNoAliasSet()
    {
        $indexConfig = new IndexConfig(['name' => 'name', 'config' => [], 'mapping' => [], 'model' => null]);
        list($index, $client) = $this->getMockedIndex('unique_name');

        $client->expects($this->at(0))
            ->method('request')
            ->with('_aliases', 'GET')
            ->willReturn(new Response([]));
        $client->expects($this->at(1))
            ->method('request')
            ->with('_aliases', 'POST', ['actions' => [
                ['add' => ['index' => 'unique_name', 'alias' => 'name']],
            ]]);

        $this->processor->switchIndexAlias($indexConfig, $index, false);
    }

    public function testSwitchAliasExistingAliasSet()
    {
        $indexConfig = new IndexConfig(['name' => 'name', 'config' => [], 'mapping' => [], 'model' => null]);
        list($index, $client) = $this->getMockedIndex('unique_name');

        $client->expects($this->at(0))
            ->method('request')
            ->with('_aliases', 'GET')
            ->willReturn(new Response([
                'old_unique_name' => ['aliases' => ['name' => []]],
            ]));
        $client->expects($this->at(1))
            ->method('request')
            ->with('_aliases', 'POST', ['actions' => [
                ['remove' => ['index' => 'old_unique_name', 'alias' => 'name']],
                ['add' => ['index' => 'unique_name', 'alias' => 'name']],
            ]]);

        $this->processor->switchIndexAlias($indexConfig, $index, false);
    }

    public function testSwitchAliasThrowsWhenMoreThanOneExists()
    {
        $indexConfig = new IndexConfig(['name' => 'name', 'config' => [], 'mapping' => [], 'model' => null]);
        list($index, $client) = $this->getMockedIndex('unique_name');

        $client->expects($this->at(0))
            ->method('request')
            ->with('_aliases', 'GET')
            ->willReturn(new Response([
                'old_unique_name' => ['aliases' => ['name' => []]],
                'another_old_unique_name' => ['aliases' => ['name' => []]],
            ]));

        $this->expectException(\RuntimeException::class);
        $this->processor->switchIndexAlias($indexConfig, $index, false);
    }

    public function testSwitchAliasThrowsWhenAliasIsAnIndex()
    {
        $indexConfig = new IndexConfig(['name' => 'name', 'config' => [], 'mapping' => [], 'model' => null]);
        list($index, $client) = $this->getMockedIndex('unique_name');

        $client->expects($this->at(0))
            ->method('request')
            ->with('_aliases', 'GET')
            ->willReturn(new Response([
                'name' => [],
            ]));

        $this->expectException(AliasIsIndexException::class);
        $this->processor->switchIndexAlias($indexConfig, $index, false);
    }

    public function testSwitchAliasDeletesIndexCollisionIfForced()
    {
        $indexConfig = new IndexConfig(['name' => 'name', 'config' => [], 'mapping' => [], 'model' => null]);
        list($index, $client) = $this->getMockedIndex('unique_name');

        $client->expects($this->at(0))
            ->method('request')
            ->with('_aliases', 'GET')
            ->willReturn(new Response([
                'name' => [],
            ]));
        $client->expects($this->at(1))
            ->method('request')
            ->with('name', 'DELETE');

        $this->processor->switchIndexAlias($indexConfig, $index, true);
    }

    public function testSwitchAliasDeletesOldIndex()
    {
        $indexConfig = new IndexConfig(['name' => 'name', 'config' => [], 'mapping' => [], 'model' => null]);
        list($index, $client) = $this->getMockedIndex('unique_name');

        $client->expects($this->at(0))
            ->method('request')
            ->with('_aliases', 'GET')
            ->willReturn(new Response([
                'old_unique_name' => ['aliases' => ['name' => []]],
            ]));
        $client->expects($this->at(1))
            ->method('request')
            ->with('_aliases', 'POST', ['actions' => [
                ['remove' => ['index' => 'old_unique_name', 'alias' => 'name']],
                ['add' => ['index' => 'unique_name', 'alias' => 'name']],
            ]]);
        $client->expects($this->at(2))
            ->method('request')
            ->with('old_unique_name', 'DELETE');

        $this->processor->switchIndexAlias($indexConfig, $index, true);
    }

    public function testSwitchAliasCleansUpOnRenameFailure()
    {
        $indexConfig = new IndexConfig(['name' => 'name', 'config' => [], 'mapping' => [], 'model' => null]);
        list($index, $client) = $this->getMockedIndex('unique_name');

        $client->expects($this->at(0))
            ->method('request')
            ->with('_aliases', 'GET')
            ->willReturn(new Response([
                'old_unique_name' => ['aliases' => ['name' => []]],
            ]));
        $client->expects($this->at(1))
            ->method('request')
            ->with('_aliases', 'POST', ['actions' => [
                ['remove' => ['index' => 'old_unique_name', 'alias' => 'name']],
                ['add' => ['index' => 'unique_name', 'alias' => 'name']],
            ]])
            ->will($this->throwException(new ResponseException(new Request(''), new Response(''))));
        $client->expects($this->at(2))
            ->method('request')
            ->with('unique_name', 'DELETE');
        // Not an annotation: we do not want a RuntimeException until now.
        $this->expectException(\RuntimeException::class);

        $this->processor->switchIndexAlias($indexConfig, $index, true);
    }

    public function getSetRootNameData()
    {
        return [
            [['name' => 'name', 'config' => [], 'mapping' => [], 'model' => null], 'name_'],
            [['elasticsearch_name' => 'notname', 'name' => 'name', 'config' => [], 'mapping' => [], 'model' => null], 'notname_'],
        ];
    }

    private function getMockedIndex(string $name)
    {
        $index = $this->createMock(Index::class);

        $client = $this->createMock(Client::class);
        $index->expects($this->any())
            ->method('getClient')
            ->willReturn($client);

        $index->expects($this->any())
            ->method('getName')
            ->willReturn($name);

        return [$index, $client];
    }
}
