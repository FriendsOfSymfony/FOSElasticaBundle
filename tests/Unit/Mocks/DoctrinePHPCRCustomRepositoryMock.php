<?php
namespace FOS\ElasticaBundle\Tests\Unit\Mocks;

use Doctrine\ODM\PHPCR\DocumentRepository;

class DoctrinePHPCRCustomRepositoryMock extends DocumentRepository
{
    public function createCustomQueryBuilder() {}
}
