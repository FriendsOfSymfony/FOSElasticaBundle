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
        if (!class_exists('Elastica_Exception_Response') || !class_exists('Elastica_Response')) {
           $this->markTestSkipped('The Elastica library classes are not available');
        }
    }

    public function testThatResetMethodRecreateAllIndexes()
    {
        $indexConfig = array();
        $indexConfig['index_1'] = array();
        $indexConfig['index_1']['index'] = new Index();
        $indexConfig['index_1']['config'] = array();
        $indexConfig['index_2'] = array();
        $indexConfig['index_2']['index'] = new Index();
        $indexConfig['index_2']['config'] = array();


        $reseter = new Reseter($indexConfig);
        $reseter->reset();

        $this->assertTrue($indexConfig['index_1']['index']->created);
        $this->assertTrue($indexConfig['index_2']['index']->created);
    }

}
