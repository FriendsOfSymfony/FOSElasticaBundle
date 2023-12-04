<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <https://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Provider;

use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\ExpressionLanguage\SyntaxError;

/**
 * @phpstan-type TCallbackInput = string|(callable(object):bool)
 * @phpstan-type TCallbackInternal = callable|string|ExpressionLanguage|null
 */
class Indexable implements IndexableInterface
{
    /**
     * An array of raw configured callbacks for all types.
     *
     * @var array
     *
     * @phpstan-var array<string, TCallbackInput>
     */
    private $callbacks = [];

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
     *
     * @phpstan-var array<string, TCallbackInternal>
     */
    private $initialisedCallbacks = [];

    /**
     * @phpstan-param array<string, TCallbackInput> $callbacks
     */
    public function __construct(array $callbacks)
    {
        $this->callbacks = $callbacks;
    }

    /**
     * Return whether the object is indexable with respect to the callback.
     */
    public function isObjectIndexable(string $indexName, object $object): bool
    {
        if (!$callback = $this->getCallback($indexName, $object)) {
            return true;
        }

        if ($callback instanceof Expression) {
            return (bool) $this->getExpressionLanguage()->evaluate($callback, [
                'object' => $object,
                $this->getExpressionVar($object) => $object,
            ]);
        }

        return \is_string($callback)
            ? \call_user_func([$object, $callback])
            : \call_user_func($callback, $object);
    }

    /**
     * Builds and initialises a callback.
     *
     * @return callable|string|ExpressionLanguage|null
     *
     * @phpstan-return TCallbackInternal
     */
    private function buildCallback(string $index, object $object)
    {
        if (!\array_key_exists($index, $this->callbacks)) {
            return null;
        }

        $callback = $this->callbacks[$index];

        if (\is_callable($callback) || \is_callable([$object, $callback])) {
            return $callback;
        }

        if (\is_string($callback)) {
            return $this->buildExpressionCallback($index, $object, $callback);
        }

        // @phpstan-ignore-next-line
        throw new \InvalidArgumentException(\sprintf('Callback for index "%s" is not a valid callback.', $index));
    }

    /**
     * Processes a string expression into an Expression.
     */
    private function buildExpressionCallback(string $index, object $object, string $callback): Expression
    {
        $expression = $this->getExpressionLanguage();
        if (!$expression) {
            throw new \RuntimeException('Unable to process an expression without the ExpressionLanguage component.');
        }

        try {
            $callback = new Expression($callback);
            $expression->compile($callback, [
                'object', $this->getExpressionVar($object),
            ]);

            return $callback;
        } catch (SyntaxError $e) {
            throw new \InvalidArgumentException(\sprintf('Callback for index "%s" is an invalid expression', $index), $e->getCode(), $e);
        }
    }

    /**
     * Retreives a cached callback, or creates a new callback if one is not found.
     *
     * @phpstan-return TCallbackInternal
     */
    private function getCallback(string $index, object $object)
    {
        if (!\array_key_exists($index, $this->initialisedCallbacks)) {
            $this->initialisedCallbacks[$index] = $this->buildCallback($index, $object);
        }

        return $this->initialisedCallbacks[$index];
    }

    /**
     * Returns the ExpressionLanguage class if it is available.
     *
     * @phpstan-ignore-next-line ExpressionLanguage may be missing -> returns null
     */
    private function getExpressionLanguage(): ?ExpressionLanguage
    {
        if (null === $this->expressionLanguage && \class_exists(ExpressionLanguage::class)) {
            $this->expressionLanguage = new ExpressionLanguage();
        }

        return $this->expressionLanguage;
    }

    /**
     * Returns the variable name to be used to access the object when using the ExpressionLanguage
     * component to parse and evaluate an expression.
     */
    private function getExpressionVar($object = null): string
    {
        if (!\is_object($object)) {
            return 'object';
        }

        $ref = new \ReflectionClass($object);

        return \strtolower($ref->getShortName());
    }
}
