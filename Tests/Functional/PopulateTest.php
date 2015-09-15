<?php
namespace FOS\ElasticaBundle\Tests\Functional;

use Doctrine\Bundle\DoctrineBundle\Command\Proxy\CreateSchemaDoctrineCommand;
use FOS\ElasticaBundle\Command\PopulateCommand;
use FOS\ElasticaBundle\Tests\Functional\DataFixtures\LoadTypeObjects;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @group functional
 */
class PopulateTest extends WebTestCase
{
    private $application;
    private $client;

    public function setUp() {
        $this->client = $this->createClient(array('test_case' => 'ORM'));
        $this->application = new Application($this->client->getKernel());
        $this->application->add(new CreateSchemaDoctrineCommand());
        $this->application->add(new PopulateCommand());
        $this->createSchema();
        $this->loadFixtures();
    }

    public function testPopulate() {
        $command = $this->application->find('fos:elastica:populate');
        $tester = new CommandTester($command);
        $tester->execute(array(
            '--batch-size' => 4,
            '--index' => 'index', 
            '--limit' => 10,
            '--no-reset' => true,
            '--offset' => 2,
            '--type' => 'type',
        ));
        $display = $tester->getDisplay();
        foreach (array(0, 2, 6, 10) as $i) { 
            $this->assertContains("$i/10", $display);
        }
        foreach (array(1, 3, 4, 5, 7, 8, 9) as $i) { 
            $this->assertNotContains("$i/10", $display);
        }
        $this->assertContains('Populating index/type', $display);
        $this->assertContains('Refreshing index', $display);
    }

    private function createSchema() {
        $command = $this->application->find('doctrine:schema:create');
        $tester = new CommandTester($command);
        $tester->execute(array());
        $this->assertContains('Database schema created successfully', $tester->getDisplay());
    }

    private function loadFixtures() {
        $manager = $this->client->getContainer()->get('doctrine')->getManager();
        $fixture = new LoadTypeObjects();
        $fixture->load($manager);
    }
}
