<?php

/**
 * This file is part of the FOSElasticaBundle project.
 *
 * (c) FriendsOfSymfony <https://github.com/FriendsOfSymfony/FOSElasticaBundle/graphs/contributors>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Provider;

use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\ExpressionLanguage\SyntaxError;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class Indexable implements IndexableInterface
{
    /**
     * An array of raw configured callbacks for all types.
     *
     * @var array
     */
    private $callbacks = array();

    /**
     * An instance of ExpressionLanguage
     *
     * @var ExpressionLanguage
     */
    private $expressionLanguage;

    /**
     * An array of initialised callbacks.
     *
     * @var array
     */
    private $initialisedCallbacks = array();

    /**
     * PropertyAccessor instance
     *
     * @var PropertyAccessorInterface
     */
    private $propertyAccessor;

    /**
     * @param array $callbacks
     */
    public function __construct(array $callbacks)
    {
        $this->callbacks = $callbacks;
        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
    }

    /**
     * Return whether the object is indexable with respect to the callback.
     *
     * @param string $indexName
     * @param string $typeName
     * @param mixed $object
     * @return bool
     */
    public function isObjectIndexable($indexName, $typeName, $object)
    {
        $type = sprintf('%s/%s', $indexName, $typeName);
        $callback = $this->getCallback($type, $object);
        if (!$callback) {
            return true;
        }

        if ($callback instanceof Expression) {
            return $this->getExpressionLanguage()->evaluate($callback, array(
                'object' => $object,
                $this->getExpressionVar($object) => $object
            ));
        }

        return is_string($callback)
            ? call_user_func(array($object, $callback))
            : call_user_func($callback, $object);
    }

    /**
     * Builds and initialises a callback.
     *
     * @param string $type
     * @param object $object
     * @return mixed
     */
    private function buildCallback($type, $object)
    {
        if (!array_key_exists($type, $this->callbacks)) {
            return null;
        }

        $callback = $this->callbacks[$type];

        if (is_callable($callback) or is_callable(array($object, $callback))) {
            return $callback;
        }

        if (is_array($callback)) {
            list($class, $method) = $callback + array(null, null);
            if (is_object($class)) {
                $class = get_class($class);
            }

            if ($class && $method) {
                throw new \InvalidArgumentException(sprintf('Callback for type "%s", "%s::%s()", is not callable.', $type, $class, $method));
            }
        }

        if (is_string($callback) && $expression = $this->getExpressionLanguage()) {
            $callback = new Expression($callback);

            try {
                $expression->compile($callback, array('object', $this->getExpressionVar($object)));

                return $callback;
            } catch (SyntaxError $e) {
                throw new \InvalidArgumentException(sprintf('Callback for type "%s" is an invalid expression', $type), $e->getCode(), $e);
            }
        }

        throw new \InvalidArgumentException(sprintf('Callback for type "%s" is not a valid callback.', $type));
    }

    /**
     * Retreives a cached callback, or creates a new callback if one is not found.
     *
     * @param string $type
     * @param object $object
     * @return mixed
     */
    private function getCallback($type, $object)
    {
        if (!array_key_exists($type, $this->initialisedCallbacks)) {
            $this->initialisedCallbacks[$type] = $this->buildCallback($type, $object);
        }

        return $this->initialisedCallbacks[$type];
    }

    /**
     * @return bool|ExpressionLanguage
     */
    private function getExpressionLanguage()
    {
        if (null === $this->expressionLanguage) {
            if (!class_exists('Symfony\Component\ExpressionLanguage\ExpressionLanguage')) {
                return false;
            }

            $this->expressionLanguage = new ExpressionLanguage();
        }

        return $this->expressionLanguage;
    }

    /**
     * @param mixed $object
     * @return string
     */
    private function getExpressionVar($object = null)
    {
        $ref = new \ReflectionClass($object);

        return strtolower($ref->getShortName());
    }
}
