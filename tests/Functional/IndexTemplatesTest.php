<?php

namespace FOS\ElasticaBundle\Tests\Functional;

use FOS\ElasticaBundle\Configuration\ConfigManager;
use FOS\ElasticaBundle\Elastica\IndexTemplate;
use Elastica\IndexTemplate as OriginalIndexTemplate;
use FOS\ElasticaBundle\Index\IndexTemplateManager;
use FOS\ElasticaBundle\Index\TemplateResetter;

/**
 * Class Index templates test
 *
 * @author Dmitry Balabka <dmitry.balabka@intexsys.lv>
 */
class IndexTemplatesTest extends WebTestCase
{
    public function testContainer()
    {
        static::bootKernel(['test_case' => 'Basic']);

        $instance = static::$kernel->getContainer()->get('fos_elastica.index_template.index_template_example_1');
        $this->assertInstanceOf(IndexTemplate::class, $instance);
        $this->assertInstanceOf(OriginalIndexTemplate::class, $instance);

        $instance = static::$kernel->getContainer()->get('fos_elastica.config_manager.index_templates');
        $this->assertInstanceOf(ConfigManager::class, $instance);

        $instance = static::$kernel->getContainer()->get('fos_elastica.index_template_manager');
        $this->assertInstanceOf(IndexTemplateManager::class, $instance);

        $instance = static::$kernel->getContainer()->get('fos_elastica.template_resetter');
        $this->assertInstanceOf(TemplateResetter::class, $instance);
    }
}
