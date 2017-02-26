<?php

namespace FOS\ElasticaBundle\PropertyAccessor;

use Symfony\Component\PropertyAccess\PropertyAccessor;

/**
 * Allow to access a property value and directly call it's __toString() method
 * Very useful for objects used as id (like UUID)
 */
class CastToStringPropertyAccessor extends PropertyAccessor
{
    /**
     * @inheritDoc
     */
    public function getValue($objectOrArray, $propertyPath)
    {
        $value = parent::getValue($objectOrArray, $propertyPath);

        return $this->cleanValue($value);
    }

    /**
     * @param mixed $value
     *
     * @return string
     */
    private function cleanValue($value)
    {
        if (true === is_object($value) && method_exists($value, '__toString')) {
            $value = (string) $value;
        }

        return $value;
    }
}
