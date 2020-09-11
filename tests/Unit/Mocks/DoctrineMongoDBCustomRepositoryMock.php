<?php
namespace FOS\ElasticaBundle\Tests\Unit\Mocks;

use Doctrine\ODM\MongoDB\Repository\DocumentRepository;

class DoctrineMongoDBCustomRepositoryMock extends DocumentRepository
{
    public function createCustomQueryBuilder() {}
}
