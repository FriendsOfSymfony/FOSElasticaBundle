<?php
/**
 * Created by PhpStorm.
 * User: dominikkasprzak
 * Date: 27/02/15
 * Time: 11:16
 */

namespace FOS\ElasticaBundle\Command;


use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

class LoggerClosureHelper
{
    /**
     * Builds a loggerClosure to be called from inside the Provider to update the command
     * line.
     *
     * @param OutputInterface $output
     * @param string $index
     * @param string $type
     * @return callable
     */
    public function getLoggerClosure(OutputInterface $output, $message, $messageParams = array())
    {
        if (!class_exists('Symfony\Component\Console\Helper\ProgressBar')) {
            $lastStep = null;
            $current = 0;

            return function ($increment, $totalObjects) use ($output, $message, $messageParams, &$lastStep, &$current) {
                if ($current + $increment > $totalObjects) {
                    $increment = $totalObjects - $current;
                }

                $message = vsprintf($message, $messageParams);

                $currentTime = microtime(true);
                $timeDifference = $currentTime - $lastStep;
                $objectsPerSecond = $lastStep ? ($increment / $timeDifference) : $increment;
                $lastStep = $currentTime;
                $current += $increment;
                $percent = 100 * $current / $totalObjects;

                $output->writeln(sprintf(
                    $message.' %0.1f%% (%d/%d), %d objects/s (RAM: current=%uMo peak=%uMo)',
                    $percent,
                    $current,
                    $totalObjects,
                    $objectsPerSecond,
                    round(memory_get_usage() / (1024 * 1024)),
                    round(memory_get_peak_usage() / (1024 * 1024))
                ));
            };
        }

        ProgressBar::setFormatDefinition('normal', " %current%/%max% [%bar%] %percent:3s%%\n%message%");
        ProgressBar::setFormatDefinition('verbose', " %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%\n%message%");
        ProgressBar::setFormatDefinition('very_verbose', " %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s%\n%message%");
        ProgressBar::setFormatDefinition('debug', " %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s%\n%message%");
        $progress = null;

        return function ($increment, $totalObjects) use (&$progress, $output, $message, $messageParams) {
            if (null === $progress) {
                $output->writeln('');
                $progress = new ProgressBar($output, $totalObjects);
                $progress->start();
            }

            $progress->setMessage(vsprintf($message, $messageParams));
            $progress->advance($increment);

            if ($progress->getProgressPercent() >= 1.0) {
                $progress->finish();
                $output->writeln('');
            }
        };
    }
}
