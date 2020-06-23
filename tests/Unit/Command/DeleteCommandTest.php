<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Tests\Unit\Command;

use Elastica\Request;
use FOS\ElasticaBundle\Command\DeleteCommand;
use FOS\ElasticaBundle\Elastica\Client;
use FOS\ElasticaBundle\Elastica\Index;
use FOS\ElasticaBundle\Index\IndexManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;

class DeleteCommandTest extends TestCase
{
    /**
     * @var DeleteCommand
     */
    private $command;

    /**
     * @var Client|\PHPUnit_Framework_MockObject_MockObject
     */
    private $clientMock;

    /**
     * @var IndexManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $indexManagerMock;

    /**
     * @var Index|\PHPUnit_Framework_MockObject_MockObject
     */
    private $indexMock;

    protected function setUp(): void
    {
        $this->clientMock = $this->createMock(Client::class);
        $this->indexManagerMock = $this->createMock(IndexManager::class);
        $this->indexMock = $this->createMock(Index::class);

        $this->command = new DeleteCommand($this->clientMock, $this->indexManagerMock);
    }

    public function testDeleteAllIndexes()
    {
        $input = $this->createMock(InputInterface::class);
        $input->expects($this->once())->method('getOption')->with('index')->willReturn(null);


        $index1 = clone $this->indexMock;
        $index2 = clone $this->indexMock;

        $this->indexManagerMock
            ->expects($this->once())
            ->method('getAllIndexes')
            ->will($this->returnValue(['index1' => true, 'index2' => true]))
        ;

        $this->indexManagerMock
            ->expects($this->exactly(2))
            ->method('getIndex')
            ->withConsecutive(['index1'], ['index2'])
            ->willReturnOnConsecutiveCalls($index1, $index2);
        ;

        $index1->expects($this->once())->method('exists')->willReturn(true);
        $index1->expects($this->once())->method('getName')->willReturn('index1');
        $index2->expects($this->once())->method('exists')->willReturn(true);
        $index2->expects($this->once())->method('getName')->willReturn('index2');

        $this->clientMock
            ->expects($this->exactly(2))
            ->method('request')
            ->withConsecutive(['index1', Request::DELETE], ['index2', Request::DELETE])
        ;

        $this->command->run(
            $input,
            new NullOutput()
        );
    }

    public function testDeleteIndex()
    {
        $input = $this->createMock(InputInterface::class);
        $input->expects($this->once())->method('getOption')->with('index')->willReturn('index_name');

        $this->indexManagerMock
            ->expects($this->never())
            ->method('getAllIndexes')
        ;

        $this->indexManagerMock
            ->expects($this->once())
            ->method('getIndex')
            ->with('index_name')
            ->willReturn($this->indexMock);
        ;

        $this->indexMock->expects($this->once())->method('exists')->willReturn(true);
        $this->indexMock->expects($this->once())->method('getName')->willReturn('index_name');

        $this->clientMock
            ->expects($this->once())
            ->method('request')
            ->with('index_name', Request::DELETE)
        ;

        $this->command->run(
            $input,
            new NullOutput()
        );
    }
}
