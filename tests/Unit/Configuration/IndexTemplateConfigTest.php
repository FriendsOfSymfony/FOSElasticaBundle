<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <https://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Tests\Unit\Configuration;

use FOS\ElasticaBundle\Configuration\IndexTemplateConfig;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class IndexTemplateConfigTest extends TestCase
{
    public function testInstantiate()
    {
        $name = 'index_template1';
        $config = [
            'elasticsearch_name' => 'index_template_elastic_name1',
            'name' => 'index_template1',
            'settings' => [1],
            'template' => 't*',
            'config' => [],
            'mapping' => [],
        ];
        $indexTemplate = new IndexTemplateConfig($config);
        $this->assertEquals($name, $indexTemplate->getName());
        $this->assertEquals(
            $config,
            [
                'elasticsearch_name' => $indexTemplate->getElasticSearchName(),
                'name' => $indexTemplate->getName(),
                'settings' => $indexTemplate->getSettings(),
                'template' => $indexTemplate->getTemplate(),
                'config' => [],
                'mapping' => [],
            ]
        );
    }

    public function testIncorrectInstantiate()
    {
        $config = [
            'elasticsearch_name' => 'index_template1',
            'name' => 'index_template_elastic_name1',
            'settings' => [1],
            'config' => [],
            'mapping' => [],
        ];

        $this->expectException(\InvalidArgumentException::class);

        new IndexTemplateConfig($config);
    }
}
