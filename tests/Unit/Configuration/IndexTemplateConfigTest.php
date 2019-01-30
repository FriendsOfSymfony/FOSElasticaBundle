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
            'elasticSearchName' => 'index_template_elastic_name1',
            'settings' => array(1),
            'template' => 't*',
        );
        $indexTemplate = new IndexTemplateConfig($name, array(), $config);
        $this->assertEquals($name, $indexTemplate->getName());
        $this->assertEquals(
            $config,
            array(
                'elasticSearchName' => $indexTemplate->getElasticSearchName(),
                'settings' => $indexTemplate->getSettings(),
                'template' => $indexTemplate->getTemplate(),
            )
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testIncorrectInstantiate()
    {
        $name = 'index_template1';
        new IndexTemplateConfig($name, array(), array());
    }
}
