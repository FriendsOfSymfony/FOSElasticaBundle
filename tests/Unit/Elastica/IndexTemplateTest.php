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
use FOS\ElasticaBundle\Tests\Unit\ProphecyTrait;
use PHPUnit\Framework\TestCase;

/**
 * @author Dmitry Balabka <dmitry.balabka@intexsys.lv>
 */
class IndexTemplateTest extends TestCase
{
    use ProphecyTrait;

    public function testInstantiate()
    {
        $template = new IndexTemplate($this->prophesize(Client::class)->reveal(), 'some_name');
        $this->assertInstanceOf(BaseIndexTemplate::class, $template);
    }
}
