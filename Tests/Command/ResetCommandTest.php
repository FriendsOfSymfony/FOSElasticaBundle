<?php

namespace FOS\ElasticaBundle\Tests\Command;


use FOS\ElasticaBundle\Command\ResetCommand;

class ResetCommandTest extends \PHPUnit_Framework_TestCase
{
    private $resetter;

    private $indexManager;

    public function setup()
    {
        $container = new Container();

        $this->resetter = $this->getMockBuilder('\FOS\ElasticaBundle\Resetter')
            ->disableOriginalConstructor()
            ->setMethods(array('resetIndex', 'resetIndexType'))
            ->getMock();

        $container->set('fos_elastica.resetter', $this->resetter);

        $this->indexManager = $this->getMockBuilder('\FOS\ElasticaBundle\IndexManager')
            ->disableOriginalConstructor()
            ->setMethods(array('getAllIndexes'))
            ->getMock();

        $this->command = new ResetCommand();
        $this->command->setContainer($container);
    }

    public function testResetAllIndexes()
    {
        $this->indexManager->expects($this->any())
            ->method('getAllIndexes')
            ->will($this->returnValue(array('index1', 'index2')));

        $this->resetter->expects($this->at(1))
            ->method('resetIndex')
            ->with($this->equalTo('index1'));

        $this->resetter->expects($this->at(2))
            ->method('resetIndex')
            ->with($this->equalTo('index2'));

        $this->command->run(
            new ArrayInput(array()),
            new NullOutput()
        );
    }
} 