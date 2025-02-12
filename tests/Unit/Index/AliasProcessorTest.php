<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <https://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Tests\Unit\Index;

use Elastic\Elasticsearch\Endpoints\Indices;
use Elastic\Elasticsearch\Exception\HttpClientException;
use Elastic\Elasticsearch\Response\Elasticsearch;
use Elastica\Client;
use FOS\ElasticaBundle\Configuration\IndexConfig;
use FOS\ElasticaBundle\Elastica\Index;
use FOS\ElasticaBundle\Exception\AliasIsIndexException;
use FOS\ElasticaBundle\Index\AliasProcessor;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
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
            ->with($this->stringStartsWith($resultStartsWith))
        ;

        $this->processor->setRootName($indexConfig, $index);
    }

    public function testSwitchAliasNoAliasSet()
    {
        $indexConfig = new IndexConfig(['name' => 'name', 'config' => [], 'mapping' => [], 'model' => null]);
        $index = $this->getIndexMock('unique_name');
        $indices = $this->getIndicesMock($this->getClientMock($index));

        $aliasesResponse = $this->createMock(Elasticsearch::class);
        $aliasesResponse->expects($this->once())
            ->method('asArray')
            ->willReturn([]);
        $indices->expects($this->once())
            ->method('getAlias')
            ->with(['name' => '*'])
            ->willreturn($aliasesResponse)
        ;

        $indices->expects($this->once())
            ->method('updateAliases')
            ->with([
                'body' => ['actions' => [
                    ['add' => ['index' => 'unique_name', 'alias' => 'name']],
                ]]
            ])
        ;

        $indices->expects($this->never())
            ->method('delete');
        $indices->expects($this->never())
            ->method('close');

        $this->processor->switchIndexAlias($indexConfig, $index, false);
    }

    public function testSwitchAliasExistingAliasSet()
    {
        $indexConfig = new IndexConfig(['name' => 'name', 'config' => [], 'mapping' => [], 'model' => null]);
        $index = $this->getIndexMock('unique_name');
        $indices = $this->getIndicesMock($this->getClientMock($index));

        $aliasesResponse = $this->createMock(Elasticsearch::class);
        $aliasesResponse->expects($this->once())
            ->method('asArray')
            ->willReturn(['old_unique_name' => ['aliases' => ['name' => []]]]);
        $indices->expects($this->once())
            ->method('getAlias')
            ->with(['name' => '*'])
            ->willreturn($aliasesResponse)
        ;

        $indices->expects($this->once())
            ->method('updateAliases')
            ->with([
                'body' => ['actions' => [
                    ['remove' => ['index' => 'old_unique_name', 'alias' => 'name']],
                    ['add' => ['index' => 'unique_name', 'alias' => 'name']],
                ]]
            ])
        ;

        $indices->expects($this->once())
            ->method('delete')
            ->with(['index' => 'old_unique_name']);
        $indices->expects($this->never())
            ->method('close');

        $this->processor->switchIndexAlias($indexConfig, $index, false);
    }

    public function testSwitchAliasThrowsWhenMoreThanOneExists()
    {
        $indexConfig = new IndexConfig(['name' => 'name', 'config' => [], 'mapping' => [], 'model' => null]);
        $index = $this->getIndexMock('unique_name');
        $indices = $this->getIndicesMock($this->getClientMock($index));

        $aliasesResponse = $this->createMock(Elasticsearch::class);
        $aliasesResponse->expects($this->once())
            ->method('asArray')
            ->willReturn([
                'old_unique_name' => ['aliases' => ['name' => []]],
                'another_old_unique_name' => ['aliases' => ['name' => []]],
            ]);
        $indices->expects($this->once())
            ->method('getAlias')
            ->with(['name' => '*'])
            ->willreturn($aliasesResponse)
        ;

        $indices->expects($this->never())
            ->method('updateAliases')
        ;

        $indices->expects($this->never())
            ->method('delete');
        $indices->expects($this->never())
            ->method('close');

        $this->expectException(\RuntimeException::class);
        $this->processor->switchIndexAlias($indexConfig, $index, false);
    }

    public function testSwitchAliasThrowsWhenAliasIsAnIndex()
    {
        $indexConfig = new IndexConfig(['name' => 'name', 'config' => [], 'mapping' => [], 'model' => null]);
        $index = $this->getIndexMock('unique_name');
        $indices = $this->getIndicesMock($this->getClientMock($index));

        $aliasesResponse = $this->createMock(Elasticsearch::class);
        $aliasesResponse->expects($this->once())
            ->method('asArray')
            ->willReturn([
                'name' => [],
            ]);
        $indices->expects($this->once())
            ->method('getAlias')
            ->with(['name' => '*'])
            ->willreturn($aliasesResponse)
        ;

        $indices->expects($this->never())
            ->method('updateAliases')
        ;

        $indices->expects($this->never())
            ->method('delete');
        $indices->expects($this->never())
            ->method('close');

        $this->expectException(AliasIsIndexException::class);
        $this->processor->switchIndexAlias($indexConfig, $index, false);
    }

    public function testSwitchAliasDeletesIndexCollisionIfForced()
    {
        $indexConfig = new IndexConfig(['name' => 'name', 'config' => [], 'mapping' => [], 'model' => null]);
        $index = $this->getIndexMock('unique_name');
        $indices = $this->getIndicesMock($this->getClientMock($index));

        $aliasesResponse = $this->createMock(Elasticsearch::class);
        $aliasesResponse->expects($this->once())
            ->method('asArray')
            ->willReturn([
                'name' => [],
            ]);
        $indices->expects($this->once())
            ->method('getAlias')
            ->with(['name' => '*'])
            ->willreturn($aliasesResponse)
        ;

        $indices->expects($this->once())
            ->method('updateAliases')
            ->with([
                'body' => ['actions' => [
                    ['add' => ['index' => 'unique_name', 'alias' => 'name']],
                ]]
            ])
        ;

        $indices->expects($this->once())
            ->method('delete')
            ->with(['index' => 'name']);
        $indices->expects($this->never())
            ->method('close');

        $this->processor->switchIndexAlias($indexConfig, $index, true);
    }

    public function testSwitchAliasCloseOldIndex()
    {
        $indexConfig = new IndexConfig(['name' => 'name', 'config' => [], 'mapping' => [], 'model' => null]);
        $index = $this->getIndexMock('unique_name');
        $indices = $this->getIndicesMock($this->getClientMock($index));

        $aliasesResponse = $this->createMock(Elasticsearch::class);
        $aliasesResponse->expects($this->once())
            ->method('asArray')
            ->willReturn(['old_unique_name' => ['aliases' => ['name' => []]]]);
        $indices->expects($this->once())
            ->method('getAlias')
            ->with(['name' => '*'])
            ->willreturn($aliasesResponse)
        ;

        $indices->expects($this->once())
            ->method('updateAliases')
            ->with([
                'body' => ['actions' => [
                    ['remove' => ['index' => 'old_unique_name', 'alias' => 'name']],
                    ['add' => ['index' => 'unique_name', 'alias' => 'name']],
                ]]
            ])
        ;

        $indices->expects($this->never())
            ->method('delete')
        ;
        $indices->expects($this->once())
            ->method('close')
            ->with(['index' => 'old_unique_name'])
        ;

        $this->processor->switchIndexAlias($indexConfig, $index, true, false);
    }

    public function testSwitchAliasCleansUpOnRenameFailure()
    {
        $indexConfig = new IndexConfig(['name' => 'name', 'config' => [], 'mapping' => [], 'model' => null]);
        $index = $this->getIndexMock('unique_name');
        $indices = $this->getIndicesMock($this->getClientMock($index));

        $aliasesResponse = $this->createMock(Elasticsearch::class);
        $aliasesResponse->expects($this->once())
            ->method('asArray')
            ->willReturn(['old_unique_name' => ['aliases' => ['name' => []]]]);
        $indices->expects($this->once())
            ->method('getAlias')
            ->with(['name' => '*'])
            ->willreturn($aliasesResponse)
        ;

        $indices->expects($this->once())
            ->method('updateAliases')
            ->with([
                'body' => ['actions' => [
                    ['remove' => ['index' => 'old_unique_name', 'alias' => 'name']],
                    ['add' => ['index' => 'unique_name', 'alias' => 'name']],
                ]]
            ])
            ->willThrowException(new HttpClientException())
        ;

        $indices->expects($this->once())
            ->method('delete')
            ->with(['index' => 'unique_name']);
        $indices->expects($this->never())
            ->method('close');

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

    private function getIndexMock(string $name): Index&MockObject
    {
        $index = $this->createMock(Index::class);

        $index->expects($this->any())
            ->method('getName')
            ->willReturn($name);

        return $index;
    }

    private function getClientMock(Index&MockObject $index): Client&MockObject
    {
        $client = $this->createMock(Client::class);

        $index->expects($this->any())
            ->method('getClient')
            ->willReturn($client);

        return $client;
    }

    /**
     * @return Indices&MockObject
     */
    private function getIndicesMock(Client&MockObject $client): Indices&MockObject
    {
        $indices = $this->createMock(Indices::class);

        $client->expects($this->any())
            ->method('indices')
            ->willReturn($indices);

        return $indices;
    }
}
