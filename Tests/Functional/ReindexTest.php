<?php
namespace FOS\ElasticaBundle\Tests\Functional;

use FOS\ElasticaBundle\Command\ReindexCommand;
use FOS\ElasticaBundle\Command\ResetCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @group functional
 */
class ReindexTest extends WebTestCase
{
    private $application;
    private $client;

    public function setUp() {
        parent::setUp();
        $this->client = $this->createClient(array('test_case' => 'Reindex'));
        $this->application = new Application($this->client->getKernel());
        $this->application->add(new ReindexCommand());
        $this->application->add(new ResetCommand());
        $this->resetIndex();
        $this->loadFixtures();
        $this->deleteTmpDir('Reindex');
    }

    public function testReindex() {
        $command = $this->application->find('fos:elastica:reindex');
        $tester = new CommandTester($command);
        $tester->execute(array(
            '--index' => 'index', 
        ));
        $display = $tester->getDisplay();
        $this->assertContains('Reindexing index', $display);
        $this->assertContains('10/10', $display);
    }

    private function resetIndex() {
        $command = $this->application->find('fos:elastica:reset');
        $tester = new CommandTester($command);
        $tester->execute(array(
            '--force' => true,
        ));
        $display = $tester->getDisplay();
        $this->assertContains('Resetting index', $display);
    }

    private function loadFixtures() {
        $persister = $this->client->getContainer()->get('fos_elastica.object_persister.index.type');
        for ($i = 0; $i < 10; ++$i) {
            $object = new TypeObj();
            $object->id = $i;
            $persister->insertOne($object);
        }
        sleep(1); // Documents aren't immediately available.
    }

    protected function tearDown() {
        parent::tearDown();
        $this->deleteTmpDir('Reindex');
    }
}