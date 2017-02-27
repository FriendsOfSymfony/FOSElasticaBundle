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
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\DependencyInjection\Container;

class ResetCommandTest extends \PHPUnit_Framework_TestCase
{
    private $command;
    private $resetter;
    private $indexManager;

    public function setup()
    {
        $container = new Container();

        $this->resetter = $this->getMockBuilder('\FOS\ElasticaBundle\Index\Resetter')
            ->disableOriginalConstructor()
            ->setMethods(array('resetIndex', 'resetIndexType'))
            ->getMock();

        $container->set('fos_elastica.resetter', $this->resetter);

        $this->indexManager = $this->getMockBuilder('\FOS\ElasticaBundle\Index\IndexManager')
            ->disableOriginalConstructor()
            ->setMethods(array('getAllIndexes'))
            ->getMock();

        $container->set('fos_elastica.index_manager', $this->indexManager);

        $this->command = new ResetCommand();
        $this->command->setContainer($container);
    }

    public function testResetAllIndexes()
    {
        $this->indexManager->expects($this->any())
            ->method('getAllIndexes')
            ->will($this->returnValue(array('index1' => true, 'index2' => true)));

        $this->resetter->expects($this->at(0))
            ->method('resetIndex')
            ->with($this->equalTo('index1'));

        $this->resetter->expects($this->at(1))
            ->method('resetIndex')
            ->with($this->equalTo('index2'));

        $this->command->run(
            new ArrayInput(array()),
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
            new ArrayInput(array('--index' => 'index1')),
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
            new ArrayInput(array('--index' => 'index1', '--type' => 'type1')),
            new NullOutput()
        );
    }
}
