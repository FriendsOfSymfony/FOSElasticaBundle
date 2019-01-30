<?php

namespace FOS\ElasticaBundle\Tests\Functional;

use Elastica\Request;
use FOS\ElasticaBundle\Elastica\Client;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @author Dmitry Balabka <dmitry.balabka@intexsys.lv>
 */
class ResetTemplatesCommandTest extends WebTestCase
{
    /**
     * Client
     *
     * @var Client
     */
    private $client;

    /**
     * Application
     *
     * @var Application
     */
    private $application;

    protected function setUp()
    {
        static::bootKernel(['test_case' => 'Basic']);
        $this->application = $application = new Application(static::$kernel);
        // required for old supported Symfony
        $application->all();

        $this->client = static::$kernel->getContainer()->get('fos_elastica.client');
    }

    public function testResetAllTemplates()
    {
        $this->clearTemplates();

        $command = $this->application->find('fos:elastica:reset-templates');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array(
            'command'  => $command->getName(),
        ));

        $output = $commandTester->getDisplay();
        $this->assertContains('Resetting all templates', $output);

        $templates = $this->fetchAllTemplates();
        $this->assertArrayHasKey('index_template_2_name', $templates);
        $this->assertArrayHasKey('index_template_1_name', $templates);
    }

    public function testResetAllTemplatesAndForceDelete()
    {
        $this->clearTemplates();

        $command = $this->application->find('fos:elastica:reset-templates');
        $commandTester = new CommandTester($command);
        $commandTester->setInputs(['yes']);
        $commandTester->execute(
            [
                'command'  => $command->getName(),
                '--force-delete' => true,
            ]
        );

        $output = $commandTester->getDisplay();
        $this->assertContains('You are going to remove all template indexes. Are you sure?', $output);
        $this->assertContains('Resetting all templates', $output);

        $templates = $this->fetchAllTemplates();
        $this->assertArrayHasKey('index_template_2_name', $templates);
        $this->assertArrayHasKey('index_template_1_name', $templates);
    }

    public function testResetExactTemplate()
    {
        $this->clearTemplates();

        $command = $this->application->find('fos:elastica:reset-templates');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array(
            'command'  => $command->getName(),
            '--index'  => 'index_template_example_1',
        ));

        $output = $commandTester->getDisplay();
        $this->assertContains('Resetting template', $output);
        $this->assertContains('index_template_example_1', $output);

        $templates = $this->fetchAllTemplates();
        $this->assertArrayHasKey('index_template_1_name', $templates);
    }

    public function testResetExactTemplateAndForceDelete()
    {
        $this->clearTemplates();

        $command = $this->application->find('fos:elastica:reset-templates');
        $commandTester = new CommandTester($command);
        $commandTester->setInputs(['yes']);
        $commandTester->execute(array(
            'command'  => $command->getName(),
            '--index'  => 'index_template_example_1',
            '--force-delete' => true,
        ));

        $output = $commandTester->getDisplay();
        $this->assertContains('You are going to remove all template indexes. Are you sure?', $output);
        $this->assertContains('Resetting template', $output);
        $this->assertContains('index_template_example_1', $output);

        $templates = $this->fetchAllTemplates();
        $this->assertArrayHasKey('index_template_1_name', $templates);
    }

    private function clearTemplates()
    {
        $this->client->request('_template/*', Request::DELETE);
    }

    private function fetchAllTemplates()
    {
        $reponse = $this->client->request('_template', Request::GET);
        return $reponse->getData();
    }
}
