<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Tests\Command;

use FOS\ElasticaBundle\Command\ResetCommand;
use FOS\ElasticaBundle\Index\IndexManager;
use FOS\ElasticaBundle\Index\Resetter;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

class ResetCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ResetCommand
     */
    private $command;

    /**
     * @var Resetter|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resetter;

    /**
     * @var IndexManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $indexManager;

    protected function setUp()
    {
        $this->resetter = $this->getMockBuilder(Resetter::class)
            ->disableOriginalConstructor()
            ->setMethods(['resetIndex', 'resetIndexType'])
            ->getMock();

        $this->indexManager = $this->getMockBuilder(IndexManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAllIndexes'])
            ->getMock();

        $this->command = new ResetCommand($this->indexManager, $this->resetter);
    }

    public function testResetAllIndexes()
    {
        $this->indexManager->expects($this->any())
            ->method('getAllIndexes')
            ->will($this->returnValue(['index1' => true, 'index2' => true]));

        $this->resetter->expects($this->at(0))
            ->method('resetIndex')
            ->with($this->equalTo('index1'));

        $this->resetter->expects($this->at(1))
            ->method('resetIndex')
            ->with($this->equalTo('index2'));

        $this->command->run(
            new ArrayInput([]),
            new NullOutput()
        );
    }

    public function testResetIndex()
    {
        $this->indexManager->expects($this->never())
            ->method('getAllIndexes');

        $this->resetter->expects($this->at(0))
            ->method('resetIndex')
            ->with($this->equalTo('index1'));

        $this->command->run(
            new ArrayInput(['--index' => 'index1']),
            new NullOutput()
        );
    }

    public function testResetIndexType()
    {
        $this->indexManager->expects($this->never())
            ->method('getAllIndexes');

        $this->resetter->expects($this->never())
            ->method('resetIndex');

        $this->resetter->expects($this->at(0))
            ->method('resetIndexType')
            ->with($this->equalTo('index1'), $this->equalTo('type1'));

        $this->command->run(
            new ArrayInput(['--index' => 'index1', '--type' => 'type1']),
            new NullOutput()
        );
    }
}
