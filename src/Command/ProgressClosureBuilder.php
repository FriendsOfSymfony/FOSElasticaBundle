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
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Command;

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @internal
 */
final class ProgressClosureBuilder
{
    /**
     * Builds a loggerClosure to be called from inside the Provider to update the command
     * line.
     *
     * @param OutputInterface $output
     * @param string          $action
     * @param string          $index
     * @param string          $type
     * @param int             $offset
     *
     * @return callable
     */
    public static function build(OutputInterface $output, $action, $index, $type, $offset)
    {
        $progress = null;

        return function ($increment, $totalObjects, $message = null) use (&$progress, $output, $action, $index, $type, $offset) {
            if (null === $progress) {
                $progress = new ProgressBar($output, $totalObjects);
                $progress->start();
                $progress->setProgress($offset);
            }

            if (null !== $message) {
                $progress->clear();
                $output->writeln(sprintf('<info>%s</info> <error>%s</error>', $action, $message));
                $progress->display();
            }

            $progress->setMessage(sprintf('<info>%s</info> <comment>%s/%s</comment>', $action, $index, $type));
            $progress->advance($increment);
        };
    }
}
