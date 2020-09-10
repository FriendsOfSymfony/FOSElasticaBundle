<?php

namespace FOS\ElasticaBundle\Tests\Configuration;

use FOS\ElasticaBundle\Configuration\IndexTemplateConfig;
use PHPUnit\Framework\TestCase;

class IndexTemplateConfigTest extends TestCase
{
    public function testInstantiate()
    {
        $name = 'index_template1';
        $config = array(
            'elasticsearch_name' => 'index_template_elastic_name1',
            'name' => 'index_template1',
            'settings' => array(1),
            'template' => 't*',
            'config' => [],
            'mapping' => [],
        );
        $indexTemplate = new IndexTemplateConfig($config);
        $this->assertEquals($name, $indexTemplate->getName());
        $this->assertEquals(
            $config,
            array(
                'elasticsearch_name' => $indexTemplate->getElasticSearchName(),
                'name' => $indexTemplate->getName(),
                'settings' => $indexTemplate->getSettings(),
                'template' => $indexTemplate->getTemplate(),
                'config' => [],
                'mapping' => [],
            )
        );
    }

    public function testIncorrectInstantiate()
    {
        $config = array(
            'elasticsearch_name' => 'index_template1',
            'name' => 'index_template_elastic_name1',
            'settings' => array(1),
            'config' => [],
            'mapping' => [],
        );

        $this->expectException(\InvalidArgumentException::class);

        new IndexTemplateConfig($config);
    }
}
