<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <https://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Tests\Functional;

use Elastica\IndexTemplate as OriginalIndexTemplate;
use FOS\ElasticaBundle\Configuration\ConfigManager;
use FOS\ElasticaBundle\Elastica\IndexTemplate;
use FOS\ElasticaBundle\Index\IndexTemplateManager;
use FOS\ElasticaBundle\Index\TemplateResetter;

/**
 * Class Index templates test.
 *
 * @author Dmitry Balabka <dmitry.balabka@intexsys.lv>
 *
 * @internal
 */
class IndexTemplatesTest extends WebTestCase
{
    public function testContainer()
    {
        self::bootKernel(['test_case' => 'Basic']);

        $instance = self::getContainer()->get('fos_elastica.index_template.index_template_example_1');
        $this->assertInstanceOf(IndexTemplate::class, $instance);
        $this->assertInstanceOf(OriginalIndexTemplate::class, $instance);

        $instance = self::getContainer()->get('fos_elastica.config_manager.index_templates');
        $this->assertInstanceOf(ConfigManager::class, $instance);

        $instance = self::getContainer()->get('fos_elastica.index_template_manager');
        $this->assertInstanceOf(IndexTemplateManager::class, $instance);

        $instance = self::getContainer()->get('fos_elastica.template_resetter');
        $this->assertInstanceOf(TemplateResetter::class, $instance);
    }
}
