<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <https://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Tests\Unit\Elastica;

use Elastica\Client;
use Elastica\IndexTemplate as BaseIndexTemplate;
use FOS\ElasticaBundle\Elastica\IndexTemplate;
use PHPUnit\Framework\TestCase;

/**
 * @author Dmitry Balabka <dmitry.balabka@intexsys.lv>
 *
 * @internal
 */
class IndexTemplateTest extends TestCase
{
    public function testInstantiate()
    {
        $template = new IndexTemplate($this->createStub(Client::class), 'some_name');
        $this->assertInstanceOf(BaseIndexTemplate::class, $template);
    }
}
