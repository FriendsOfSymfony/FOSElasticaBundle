<?php
namespace FOS\ElasticaBundle\Tests\Mocks;

use Doctrine\ODM\MongoDB\DocumentRepository;

class DoctrineMongoDBCustomRepositoryMock extends DocumentRepository
{
    public function createCustomQueryBuilder() {}
}
