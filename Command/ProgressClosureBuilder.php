<?php

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

class ProgressClosureBuilder
{
    /**
     * Builds a loggerClosure to be called from inside the Provider to update the command
     * line.
     *
     * @param OutputInterface $output
     * @param string $action
     * @param string $index
     * @param string $type
     * @return callable
     */
    public function build(OutputInterface $output, $action, $index, $type)
    {
        if (!class_exists('Symfony\Component\Console\Helper\ProgressBar') ||
            !is_callable(array('Symfony\Component\Console\Helper\ProgressBar', 'getProgress'))) {
            return $this->buildLegacy($output, $action, $index, $type);
        }

        $progress = null;

        return function ($increment, $totalObjects) use (&$progress, $output, $action, $index, $type) {
            if (null === $progress) {
                $progress = new ProgressBar($output, $totalObjects);
                $progress->start();
            }

            $progress->setMessage(sprintf('<info>%s</info> <comment>%s/%s</comment>', $action, $index, $type));
            $progress->advance($increment);
        };
    }

    /**
     * Builds a legacy closure that outputs lines for each step. Used in cases
     * where the ProgressBar component doesnt exist or does not have the correct
     * methods to support what we need.
     *
     * @param OutputInterface $output
     * @param string $action
     * @param string $index
     * @param string $type
     * @return callable
     */
    private function buildLegacy(OutputInterface $output, $action, $index, $type)
    {
        $lastStep = null;
        $current = 0;

        return function ($increment, $totalObjects) use ($output, $action, $index, $type, &$lastStep, &$current) {
            if ($current + $increment > $totalObjects) {
                $increment = $totalObjects - $current;
            }

            $currentTime = microtime(true);
            $timeDifference = $currentTime - $lastStep;
            $objectsPerSecond = $lastStep ? ($increment / $timeDifference) : $increment;
            $lastStep = $currentTime;
            $current += $increment;
            $percent = 100 * $current / $totalObjects;

            $output->writeln(sprintf(
                '<info>%s</info> <comment>%s/%s</comment> %0.1f%% (%d/%d), %d objects/s (RAM: current=%uMo peak=%uMo)',
                $action,
                $index,
                $type,
                $percent,
                $current,
                $totalObjects,
                $objectsPerSecond,
                round(memory_get_usage() / (1024 * 1024)),
                round(memory_get_peak_usage() / (1024 * 1024))
            ));
        };
    }
}
