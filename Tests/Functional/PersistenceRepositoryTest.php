<?php

namespace FOS\ElasticaBundle\Tests\Functional;

class PersistenceRepositoryTest extends WebTestCase
{
    public function testRepositoryShouldBeSetCorrectly()
    {
        $client = $this->createClient(array('test_case' => 'ORM'));

        $repository = $client->getContainer()->get('fos_elastica.manager.orm')
            ->getRepository('FOS\ElasticaBundle\Tests\Functional\TypeObject');

        $this->assertNotNull($repository);
        $this->assertEquals('FOS\ElasticaBundle\Tests\Functional\TypeObjectRepository', get_class($repository));
    }

    protected function setUp()
    {
        parent::setUp();

        $this->deleteTmpDir('Basic');
    }

    protected function tearDown()
    {
        parent::tearDown();

        $this->deleteTmpDir('Basic');
    }
}