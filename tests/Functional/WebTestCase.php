<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * This file is part of the FOSElasticaBundle project.
 *
 * (c) Tim Nagel <tim@nagel.com.au>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace FOS\ElasticaBundle\Tests\Functional;

use FOS\ElasticaBundle\Tests\Functional\app\AppKernel;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase as BaseKernelTestCase;
use Symfony\Component\Filesystem\Filesystem;

/*
 * Based on https://github.com/symfony/symfony/blob/2.7/src/Symfony/Bundle/FrameworkBundle/Tests/Functional/WebTestCase.php
 */
class WebTestCase extends BaseKernelTestCase
{
    protected static function getKernelClass()
    {
        require_once __DIR__.'/app/AppKernel.php';

        return AppKernel::class;
    }

    public static function setUpBeforeClass(): void
    {
        static::deleteTmpDir();
    }

    public static function tearDownAfterClass(): void
    {
        static::deleteTmpDir();
    }

    protected static function deleteTmpDir()
    {
        if (!file_exists($dir = sys_get_temp_dir().'/'.static::getVarDir())) {
            return;
        }
        $fs = new Filesystem();
        $fs->remove($dir);
    }

    protected static function createKernel(array $options = [])
    {
        $class = self::getKernelClass();

        if (!isset($options['test_case'])) {
            throw new \InvalidArgumentException('The option "test_case" must be set.');
        }

        return new $class(
            static::getVarDir(),
            $options['test_case'],
            isset($options['root_config']) ? $options['root_config'] : 'config.yml',
            isset($options['environment']) ? $options['environment'] : strtolower(static::getVarDir().$options['test_case']),
            isset($options['debug']) ? $options['debug'] : true
        );
    }

    protected static function getVarDir()
    {
        return substr(strrchr(get_called_class(), '\\'), 1);
    }
}
