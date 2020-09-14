<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
     * Client.
     *
     * @var Client
     */
    private $client;

    /**
     * Application.
     *
     * @var Application
     */
    private $application;

    protected function setUp(): void
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
        $commandTester->execute([
            'command' => $command->getName(),
        ]);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Resetting all templates', $output);

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
                'command' => $command->getName(),
                '--force-delete' => true,
            ]
        );

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('You are going to remove all template indexes. Are you sure?', $output);
        $this->assertStringContainsString('Resetting all templates', $output);

        $templates = $this->fetchAllTemplates();
        $this->assertArrayHasKey('index_template_2_name', $templates);
        $this->assertArrayHasKey('index_template_1_name', $templates);
    }

    public function testResetExactTemplate()
    {
        $this->clearTemplates();

        $command = $this->application->find('fos:elastica:reset-templates');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            '--index' => 'index_template_example_1',
        ]);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Resetting template', $output);
        $this->assertStringContainsString('index_template_example_1', $output);

        $templates = $this->fetchAllTemplates();
        $this->assertArrayHasKey('index_template_1_name', $templates);
    }

    public function testResetExactTemplateAndForceDelete()
    {
        $this->clearTemplates();

        $command = $this->application->find('fos:elastica:reset-templates');
        $commandTester = new CommandTester($command);
        $commandTester->setInputs(['yes']);
        $commandTester->execute([
            'command' => $command->getName(),
            '--index' => 'index_template_example_1',
            '--force-delete' => true,
        ]);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('You are going to remove all template indexes. Are you sure?', $output);
        $this->assertStringContainsString('Resetting template', $output);
        $this->assertStringContainsString('index_template_example_1', $output);

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
