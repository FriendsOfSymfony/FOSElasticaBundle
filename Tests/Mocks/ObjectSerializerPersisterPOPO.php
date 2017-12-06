<?php
namespace FOS\ElasticaBundle\Tests\Mocks;

class ObjectSerializerPersisterPOPO
{
    public $id   = 123;
    public $name = 'popoName';

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }
}