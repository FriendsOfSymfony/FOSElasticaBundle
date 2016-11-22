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

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\ExpressionLanguage\SyntaxError;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class Indexable implements IndexableInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * An array of raw configured callbacks for all types.
     *
     * @var array
     */
    private $callbacks = array();

    /**
     * An instance of ExpressionLanguage.
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
     * PropertyAccessor instance.
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
     * @param mixed  $object
     *
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
            return (bool) $this->getExpressionLanguage()->evaluate($callback, array(
                'object' => $object,
                $this->getExpressionVar($object) => $object,
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
     *
     * @return mixed
     */
    private function buildCallback($type, $object)
    {
        if (!array_key_exists($type, $this->callbacks)) {
            return;
        }

        $callback = $this->callbacks[$type];

        if (is_callable($callback) or is_callable(array($object, $callback))) {
            return $callback;
        }

        if (is_array($callback) && !is_object($callback[0])) {
            return $this->processArrayToCallback($type, $callback);
        }

        if (is_string($callback)) {
            return $this->buildExpressionCallback($type, $object, $callback);
        }

        throw new \InvalidArgumentException(sprintf('Callback for type "%s" is not a valid callback.', $type));
    }

    /**
     * Processes a string expression into an Expression.
     *
     * @param string $type
     * @param mixed $object
     * @param string $callback
     *
     * @return Expression
     */
    private function buildExpressionCallback($type, $object, $callback)
    {
        $expression = $this->getExpressionLanguage();
        if (!$expression) {
            throw new \RuntimeException('Unable to process an expression without the ExpressionLanguage component.');
        }

        try {
            $callback = new Expression($callback);
            $expression->compile($callback, array(
                'object', $this->getExpressionVar($object)
            ));

            return $callback;
        } catch (SyntaxError $e) {
            throw new \InvalidArgumentException(sprintf(
                'Callback for type "%s" is an invalid expression',
                $type
            ), $e->getCode(), $e);
        }
    }

    /**
     * Retreives a cached callback, or creates a new callback if one is not found.
     *
     * @param string $type
     * @param object $object
     *
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
     * Returns the ExpressionLanguage class if it is available.
     *
     * @return ExpressionLanguage|null
     */
    private function getExpressionLanguage()
    {
        if (null === $this->expressionLanguage) {
            $this->expressionLanguage = new ExpressionLanguage();
        }

        return $this->expressionLanguage;
    }

    /**
     * Returns the variable name to be used to access the object when using the ExpressionLanguage
     * component to parse and evaluate an expression.
     *
     * @param mixed $object
     *
     * @return string
     */
    private function getExpressionVar($object = null)
    {
        if (!is_object($object)) {
            return 'object';
        }

        $ref = new \ReflectionClass($object);

        return strtolower($ref->getShortName());
    }

    /**
     * Processes an array into a callback. Replaces the first element with a service if
     * it begins with an @.
     *
     * @param string $type
     * @param array $callback
     *
     * @return array
     */
    private function processArrayToCallback($type, array $callback)
    {
        list($class, $method) = $callback + array(null, '__invoke');

        if (strpos($class, '@') === 0) {
            $service = $this->container->get(substr($class, 1));
            $callback = array($service, $method);

            if (!is_callable($callback)) {
                throw new \InvalidArgumentException(sprintf(
                    'Method "%s" on service "%s" is not callable.',
                    $method,
                    substr($class, 1)
                ));
            }

            return $callback;
        }

        throw new \InvalidArgumentException(sprintf(
            'Unable to parse callback array for type "%s"',
            $type
        ));
    }
}
