<?php

namespace FOS\ElasticaBundle\Tests\Command;

use FOS\ElasticaBundle\Command\ResetCommand;
use FOS\ElasticaBundle\Index\IndexManager;
use FOS\ElasticaBundle\Index\Resetter;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\DependencyInjection\Container;

class ResetCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ResetCommand
     */
    private $command;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Resetter
     */
    private $resetter;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|IndexManager
     */
    private $indexManager;

    public function setup()
    {
        $container = new Container();

        $this->resetter = $this->getMockBuilder('\FOS\ElasticaBundle\Resetter')
            ->disableOriginalConstructor()
            ->setMethods(array('resetIndex', 'resetIndexType', 'resetAllTemplates', 'resetTemplate'))
            ->getMock();

        $container->set('fos_elastica.resetter', $this->resetter);

        $this->indexManager = $this->getMockBuilder('\FOS\ElasticaBundle\IndexManager')
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
            ->method('resetAllTemplates');

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

    public function testResetIndex()
    {
        $this->indexManager->expects($this->never())
            ->method('getAllIndexes');

        $this->resetter->expects($this->at(0))
            ->method('resetAllTemplates');

        $this->resetter->expects($this->at(1))
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
            ->method('resetAllTemplates');

        $this->resetter->expects($this->at(1))
            ->method('resetIndexType')
            ->with($this->equalTo('index1'), $this->equalTo('type1'));

        $this->command->run(
            new ArrayInput(array('--index' => 'index1', '--type' => 'type1')),
            new NullOutput()
        );
    }

    public function testResetAllIndexTemplates()
    {
        $this->resetter->expects($this->once())
            ->method('resetAllTemplates')
            ->with(false)
        ;

        $this->resetter->expects($this->never())
            ->method('resetIndex');

        $this->command->run(
            new ArrayInput(array('--index-template' => null)),
            new NullOutput()
        );
    }

    public function testResetAndDeleteAllIndexTemplates()
    {
        $this->resetter->expects($this->once())
            ->method('resetAllTemplates')
            ->with(true)
        ;

        $this->resetter->expects($this->never())
            ->method('resetIndex');

        $this->command->run(
            new ArrayInput(array('--index-template' => null, '--delete-template-indexes' => null)),
            new NullOutput()
        );
    }

    public function testResetOneIndexTemplate()
    {
        $this->resetter->expects($this->once())
            ->method('resetTemplate')
            ->with('template name', false)
        ;

        $this->resetter->expects($this->never())
            ->method('resetIndex');

        $this->command->run(
            new ArrayInput(array('--index-template' => 'template name')),
            new NullOutput()
        );
    }

    public function testResetAndDeleteOneIndexTemplate()
    {
        $this->resetter->expects($this->once())
            ->method('resetTemplate')
            ->with('template name', true)
        ;

        $this->resetter->expects($this->never())
            ->method('resetIndex');

        $this->command->run(
            new ArrayInput(array('--index-template' => 'template name', '--delete-template-indexes' => null)),
            new NullOutput()
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testThrowExceptionWhenIndexAndIndexTemplateProvidedInSameTime()
    {
        $this->command->run(
            new ArrayInput(array('--index-template' => null, '--index' => 'some template')),
            new NullOutput()
        );
    }
}
