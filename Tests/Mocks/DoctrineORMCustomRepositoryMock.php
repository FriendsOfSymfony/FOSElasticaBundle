<?php
namespace FOS\ElasticaBundle\Tests\Mocks;

use Doctrine\ORM\EntityRepository;

class DoctrineORMCustomRepositoryMock extends EntityRepository
{
    public function createCustomQueryBuilder() {}
}
