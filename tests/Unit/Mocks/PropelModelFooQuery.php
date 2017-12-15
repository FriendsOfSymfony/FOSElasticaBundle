<?php
namespace FOS\ElasticaBundle\Tests\Unit\Mocks;

class PropelModelFooQuery extends \ModelCriteria
{
    public static $latestCreatedInstance;

    public function __construct()
    {
        // no need to call parent constructor
    }

    public static function create()
    {
        return static::$latestCreatedInstance = new static();
    }
}
