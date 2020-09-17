<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <https://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Tests\Unit\Configuration\Source;

use FOS\ElasticaBundle\Configuration\IndexTemplateConfig;
use FOS\ElasticaBundle\Configuration\Source\TemplateContainerSource;
use PHPUnit\Framework\TestCase;

/**
 * @author Dmitry Balabka <dmitry.balabka@intexsys.lv>
 */
class TemplateContainerSourceTest extends TestCase
{
    public function testGetEmptyConfiguration()
    {
        $containerSource = new TemplateContainerSource([]);
        $indexes = $containerSource->getConfiguration();
        $this->assertSame([], $indexes);
    }

    public function testGetConfiguration()
    {
        $containerSource = new TemplateContainerSource(
            [
                [
                    'name' => 'some_index_template',
                    'mapping' => [
                        'some_field' => [],
                    ],
                    'config' => [
                        'date_detection' => false,
                    ],
                    'elasticsearch_name' => 'some_search_name',
                    'settings' => [
                        'some_setting' => 'setting_value',
                    ],
                    'template' => 'some_index_config_*',
                ],
            ]
        );
        $indexes = $containerSource->getConfiguration();
        $this->assertInstanceOf(IndexTemplateConfig::class, $indexes['some_index_template']);
        $templateConfig = $indexes['some_index_template'];
        $this->assertEquals('some_index_template', $templateConfig->getName());
        $this->assertEquals('some_index_config_*', $templateConfig->getTemplate());
        $this->assertEquals(
            [
                'some_setting' => 'setting_value',
            ],
            $templateConfig->getSettings()
        );
        $this->assertEquals('some_search_name', $templateConfig->getElasticSearchName());
        $this->assertEquals('some_index_template', $templateConfig->getName());
        $this->assertEquals(['some_field' => []], $templateConfig->getMapping());
        $this->assertEquals(false, $templateConfig->getDateDetection());
    }
}
