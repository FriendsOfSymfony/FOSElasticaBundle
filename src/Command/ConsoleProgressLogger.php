<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <https://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Command;

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * A process logger used from to update the command line output.
 *
 * @internal
 */
final class ConsoleProgressLogger
{
    private ?ProgressBar $progress = null;
    private readonly OutputInterface $output;
    private int $filteredCount = 0;
    private bool $finished = false;

    public function __construct(OutputInterface $output, private readonly string $action, private readonly string $index, private readonly int $offset)
    {
        $this->output = $output;
    }

    public function call(int $increment, int $filteredIncrement, int $totalObjects, ?string $message = null): void
    {
        if ($this->finished) {
            return;
        }

        if (!$this->progress instanceof ProgressBar) {
            $this->progress = new ProgressBar($this->output, $totalObjects);
            $this->progress->setMessage(\sprintf('<info>%s</info> <comment>%s</comment>', $this->action, $this->index));
            $this->progress->start();
            $this->progress->setProgress($this->offset);
        }

        if (0 !== $filteredIncrement) {
            $this->filteredCount += $filteredIncrement;
            $this->progress->setMaxSteps($totalObjects - $this->filteredCount);
            $this->progress->setMessage(\sprintf('<info>%s</info> <comment>%s</comment> (%d/%d filtered)', $this->action, $this->index, $this->filteredCount, $totalObjects));
        }

        if (null !== $message) {
            $this->progress->clear();
            $this->output->writeln(\sprintf('<info>%s</info> <error>%s</error>', $this->action, $message));
            $this->progress->display();
        }

        $this->progress->advance($increment);
    }

    public function finish(): void
    {
        $this->finished = true;
        if (!$this->progress instanceof ProgressBar) {
            return;
        }

        $this->progress->finish();
        $this->output->writeln('');
    }
}
