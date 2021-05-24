<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <https://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Tests\Unit\Transformer;

use FOS\ElasticaBundle\Tests\Unit\UnitTestHelper;
use FOS\ElasticaBundle\Transformer\AbstractElasticaToModelTransformer;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * @internal
 */
class AbstractElasticaToModelTransformerTest extends UnitTestHelper
{
    public function testSetPropertyAccessor()
    {
        $propertyAccessor = $this->mockPropertyAccesor();
        $transformer = $this->mockAbstractElasticaToModelTransformer();
        $transformer->setPropertyAccessor($propertyAccessor);
        $this->assertEquals($propertyAccessor, $this->getProtectedProperty($transformer, 'propertyAccessor'));
    }

    protected function mockAbstractElasticaToModelTransformer()
    {
        return $this
            ->getMockBuilder(AbstractElasticaToModelTransformer::class)
            ->getMockForAbstractClass()
        ;
    }

    protected function mockPropertyAccesor()
    {
        return $this->createMock(PropertyAccessorInterface::class);
    }
}
