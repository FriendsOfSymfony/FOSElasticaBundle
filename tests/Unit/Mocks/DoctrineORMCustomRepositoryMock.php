<?php
namespace FOS\ElasticaBundle\Tests\Unit\Mocks;

use Doctrine\ORM\EntityRepository;

class DoctrineORMCustomRepositoryMock extends EntityRepository
{
    public function createCustomQueryBuilder() {}
}
