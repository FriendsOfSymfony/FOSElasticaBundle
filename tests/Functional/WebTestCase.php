<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <https://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Tests\Functional;

use FOS\ElasticaBundle\Tests\Functional\app\AppKernel;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase as BaseKernelTestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;

/*
 * Based on https://github.com/symfony/symfony/blob/2.7/src/Symfony/Bundle/FrameworkBundle/Tests/Functional/WebTestCase.php
 */
/**
 * @internal
 */
class WebTestCase extends BaseKernelTestCase
{
    public static function setUpBeforeClass(): void
    {
        static::deleteTmpDir();
    }

    public static function tearDownAfterClass(): void
    {
        static::deleteTmpDir();
    }

    protected static function getKernelClass(): string
    {
        require_once __DIR__.'/app/AppKernel.php';

        return AppKernel::class;
    }

    protected static function deleteTmpDir()
    {
        if (!\file_exists($dir = \sys_get_temp_dir().'/'.static::getVarDir())) {
            return;
        }
        $fs = new Filesystem();
        $fs->remove($dir);
    }

    protected static function createKernel(array $options = []): KernelInterface
    {
        $class = self::getKernelClass();

        if (!isset($options['test_case'])) {
            throw new \InvalidArgumentException('The option "test_case" must be set.');
        }

        return new $class(
            static::getVarDir(),
            $options['test_case'],
            $options['root_config'] ?? 'config.yml',
            $options['environment'] ?? \strtolower(static::getVarDir().$options['test_case']),
            $options['debug'] ?? true
        );
    }

    protected static function getVarDir()
    {
        return \substr(\strrchr(static::class, '\\'), 1);
    }

    /**
     * To be removed when dropping support of Symfony < 5.3.
     *
     * @param mixed $arguments
     */
    public static function __callStatic(string $name, $arguments)
    {
        if ('getContainer' === $name) {
            if (\method_exists(BaseKernelTestCase::class, $name)) {
                return self::getContainer();
            }

            return self::$container;
        }

        throw new \BadMethodCallException("Method {$name} is not supported.");
    }
}
