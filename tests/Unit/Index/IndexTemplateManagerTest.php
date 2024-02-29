<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <https://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Tests\Unit\Index;

use Elastica\IndexTemplate;
use FOS\ElasticaBundle\Index\IndexTemplateManager;
use PHPUnit\Framework\TestCase;

/**
 * @author Dmitry Balabka <dmitry.balabka@intexsys.lv>
 *
 * @internal
 */
class IndexTemplateManagerTest extends TestCase
{
    /**
     * Test get index template.
     *
     * @param string      $name
     * @param string|null $expectedException
     * @param mixed       $expectedTemplate
     *
     * @return void
     *
     * @dataProvider provideTestGetIndexTemplate
     */
    public function testGetIndexTemplate(array $templates, $name, $expectedTemplate, $expectedException = null)
    {
        if (null !== $expectedException) {
            $this->expectException($expectedException);
        }
        $templateManager = new IndexTemplateManager($templates);
        $this->assertSame($expectedTemplate, $templateManager->getIndexTemplate($name));
    }

    public function provideTestGetIndexTemplate()
    {
        return [
            'empty templates' => [
                'templates' => [],
                'name' => 'any template',
                'expectedTemplate' => [],
                'expectedException' => \InvalidArgumentException::class,
            ],
            'expected template found' => [
                'templates' => [
                    'first template' => $firstTemplate = $this->createStub(IndexTemplate::class),
                    'second template' => $secondTemplate = $this->createStub(IndexTemplate::class),
                ],
                'name' => 'second template',
                'expectedTemplate' => $secondTemplate,
            ],
            'expected template not found' => [
                'templates' => [
                    'first template' => $firstTemplate = $this->createStub(IndexTemplate::class),
                    'second template' => $secondTemplate = $this->createStub(IndexTemplate::class),
                ],
                'name' => 'some template',
                'expectedTemplate' => null,
                'expectedException' => \InvalidArgumentException::class,
            ],
        ];
    }
}
