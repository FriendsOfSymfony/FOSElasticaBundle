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

namespace FOS\ElasticaBundle\Tests\Index;

use Elastica\Exception\ResponseException;
use Elastica\Request;
use Elastica\Response;
use FOS\ElasticaBundle\Configuration\IndexConfig;
use FOS\ElasticaBundle\Index\AliasProcessor;

class AliasProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AliasProcessor
     */
    private $processor;

    /**
     * @dataProvider getSetRootNameData
     *
     * @param string $name
     * @param array  $configArray
     * @param string $resultStartsWith
     */
    public function testSetRootName($name, $configArray, $resultStartsWith)
    {
        $indexConfig = new IndexConfig($name, array(), $configArray);
        $index = $this->getMockBuilder('FOS\\ElasticaBundle\\Elastica\\Index')
            ->disableOriginalConstructor()
            ->getMock();
        $index->expects($this->once())
            ->method('overrideName')
            ->with($this->stringStartsWith($resultStartsWith));

        $this->processor->setRootName($indexConfig, $index);
    }

    public function testSwitchAliasNoAliasSet()
    {
        $indexConfig = new IndexConfig('name', array(), array());
        list($index, $client) = $this->getMockedIndex('unique_name');

        $client->expects($this->at(0))
            ->method('request')
            ->with('_aliases', 'GET')
            ->willReturn(new Response(array()));
        $client->expects($this->at(1))
            ->method('request')
            ->with('_aliases', 'POST', array('actions' => array(
                array('add' => array('index' => 'unique_name', 'alias' => 'name')),
            )));

        $this->processor->switchIndexAlias($indexConfig, $index, false);
    }

    public function testSwitchAliasExistingAliasSet()
    {
        $indexConfig = new IndexConfig('name', array(), array());
        list($index, $client) = $this->getMockedIndex('unique_name');

        $client->expects($this->at(0))
            ->method('request')
            ->with('_aliases', 'GET')
            ->willReturn(new Response(array(
                'old_unique_name' => array('aliases' => array('name')),
            )));
        $client->expects($this->at(1))
            ->method('request')
            ->with('_aliases', 'POST', array('actions' => array(
                array('remove' => array('index' => 'old_unique_name', 'alias' => 'name')),
                array('add' => array('index' => 'unique_name', 'alias' => 'name')),
            )));

        $this->processor->switchIndexAlias($indexConfig, $index, false);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testSwitchAliasThrowsWhenMoreThanOneExists()
    {
        $indexConfig = new IndexConfig('name', array(), array());
        list($index, $client) = $this->getMockedIndex('unique_name');

        $client->expects($this->at(0))
            ->method('request')
            ->with('_aliases', 'GET')
            ->willReturn(new Response(array(
                'old_unique_name' => array('aliases' => array('name')),
                'another_old_unique_name' => array('aliases' => array('name')),
            )));

        $this->processor->switchIndexAlias($indexConfig, $index, false);
    }

    /**
     * @expectedException \FOS\ElasticaBundle\Exception\AliasIsIndexException
     */
    public function testSwitchAliasThrowsWhenAliasIsAnIndex()
    {
        $indexConfig = new IndexConfig('name', array(), array());
        list($index, $client) = $this->getMockedIndex('unique_name');

        $client->expects($this->at(0))
            ->method('request')
            ->with('_aliases', 'GET')
            ->willReturn(new Response(array(
                'name' => array(),
            )));

        $this->processor->switchIndexAlias($indexConfig, $index, false);
    }

    public function testSwitchAliasDeletesIndexCollisionIfForced()
    {
        $indexConfig = new IndexConfig('name', array(), array());
        list($index, $client) = $this->getMockedIndex('unique_name');

        $client->expects($this->at(0))
            ->method('request')
            ->with('_aliases', 'GET')
            ->willReturn(new Response(array(
                'name' => array(),
            )));
        $client->expects($this->at(1))
            ->method('request')
            ->with('name', 'DELETE');

        $this->processor->switchIndexAlias($indexConfig, $index, true);
    }

    public function testSwitchAliasDeletesOldIndex()
    {
        $indexConfig = new IndexConfig('name', array(), array());
        list($index, $client) = $this->getMockedIndex('unique_name');

        $client->expects($this->at(0))
            ->method('request')
            ->with('_aliases', 'GET')
            ->willReturn(new Response(array(
                'old_unique_name' => array('aliases' => array('name')),
            )));
        $client->expects($this->at(1))
            ->method('request')
            ->with('_aliases', 'POST', array('actions' => array(
                array('remove' => array('index' => 'old_unique_name', 'alias' => 'name')),
                array('add' => array('index' => 'unique_name', 'alias' => 'name')),
            )));
        $client->expects($this->at(2))
            ->method('request')
            ->with('old_unique_name', 'DELETE');

        $this->processor->switchIndexAlias($indexConfig, $index, true);
    }

    public function testSwitchAliasCleansUpOnRenameFailure()
    {
        $indexConfig = new IndexConfig('name', array(), array());
        list($index, $client) = $this->getMockedIndex('unique_name');

        $client->expects($this->at(0))
            ->method('request')
            ->with('_aliases', 'GET')
            ->willReturn(new Response(array(
                'old_unique_name' => array('aliases' => array('name')),
            )));
        $client->expects($this->at(1))
            ->method('request')
            ->with('_aliases', 'POST', array('actions' => array(
                array('remove' => array('index' => 'old_unique_name', 'alias' => 'name')),
                array('add' => array('index' => 'unique_name', 'alias' => 'name')),
            )))
            ->will($this->throwException(new ResponseException(new Request(''), new Response(''))));
        $client->expects($this->at(2))
            ->method('request')
            ->with('unique_name', 'DELETE');
        // Not an annotation: we do not want a RuntimeException until now.
        $this->setExpectedException('RuntimeException');

        $this->processor->switchIndexAlias($indexConfig, $index, true);
    }

    public function getSetRootNameData()
    {
        return array(
            array('name', array(), 'name_'),
            array('name', array('elasticSearchName' => 'notname'), 'notname_'),
        );
    }

    protected function setUp()
    {
        $this->processor = new AliasProcessor();
    }

    private function getMockedIndex($name)
    {
        $index = $this->getMockBuilder('FOS\\ElasticaBundle\\Elastica\\Index')
            ->disableOriginalConstructor()
            ->getMock();

        $client = $this->getMockBuilder('Elastica\\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $index->expects($this->any())
            ->method('getClient')
            ->willReturn($client);

        $index->expects($this->any())
            ->method('getName')
            ->willReturn($name);

        return array($index, $client);
    }
}
