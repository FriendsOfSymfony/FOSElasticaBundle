<?php

namespace FOQ\ElasticaBundle\Tests\Reseter;

use FOQ\ElasticaBundle\Reseter;
use FOQ\ElasticaBundle\IndexManager;
use Elastica_Exception_Response;
use Elastica_Response;

class Index
{
    public $deleted = false;
    public $created = false;

    public function delete()
    {
        $this->deleted = true;
    }

    public function create()
    {
       $this->created = true;
    }
}

class NewIndex
{
    public $deleted = false;
    public $created = false;

    public function delete()
    {
        $jsonResponse = json_encode(array('index' => 'is_new'));

        throw new Elastica_Exception_Response(new Elastica_Response($jsonResponse));
    }

    public function create()
    {
       $this->created = true;
    }
}

class ReseterTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
      if (!class_exists('Elastica_Exception_Response') || !class_exists('Elastica_Response'))
      {
        $this->markTestSkipped('The Elastica library classes are not available');
      }
    }

    public function testThatResetMethodDeleteAllIndexes()
    {
       $indexManager = new IndexManager(array(
           'index_1' => new Index(),
           'index_2' => new Index()
       ), new Index());
       
       $reseter = new Reseter($indexManager);
       $reseter->reset();

       $this->assertTrue($indexManager->getIndex('index_1')->deleted);
       $this->assertTrue($indexManager->getIndex('index_2')->deleted);
    }

    public function testThatResetMethodDoesNotDeleteNewIndexes()
    {
       $indexManager = new IndexManager(array(
           'index_1' => new Index(),
           'index_2' => new NewIndex()
       ), new Index());
       
       $reseter = new Reseter($indexManager);
       $reseter->reset();

       $this->assertTrue($indexManager->getIndex('index_1')->deleted);
       $this->assertFalse($indexManager->getIndex('index_2')->deleted);
    }
    
    public function testThatResetMethodRecreateAllIndexes()
    {
       $indexManager = new IndexManager(array(
           'index_1' => new Index(),
           'index_2' => new Index()
       ), new Index());
       
       $reseter = new Reseter($indexManager);
       $reseter->reset();

       $this->assertTrue($indexManager->getIndex('index_1')->created);
       $this->assertTrue($indexManager->getIndex('index_2')->created);
    }
    
    public function testThatResetMethodCreateNewIndexes()
    {
       $indexManager = new IndexManager(array(
          'index_1' => new NewIndex(),
          'index_2' => new NewIndex()
       ), new NewIndex());
       
       $reseter = new Reseter($indexManager);
       $reseter->reset();

       $this->assertTrue($indexManager->getIndex('index_1')->created);
       $this->assertTrue($indexManager->getIndex('index_2')->created);
    }
}
