<?php
namespace FOS\ElasticaBundle\Tests\Mocks;

use Doctrine\ODM\PHPCR\DocumentRepository;

class DoctrinePHPCRCustomRepositoryMock extends DocumentRepository
{
    public function createCustomQueryBuilder() {}
}
