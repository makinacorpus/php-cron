<?php

declare(strict_types=1);

namespace MakinaCorpus\Cron;

use MakinaCorpus\ArgumentResolver\ArgumentResolver;
use MakinaCorpus\Cron\TaskStateStore\ArrayTaskStateStore;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;

/**
 * Where the magic happens: run them all.
 */
class CronRunner implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    private TaskStateStore $taskStateStore;

    public function __construct(
        private TaskRegistry $taskRegistry,
        ?TaskStateStore $taskStateStore = null,
        private ?ArgumentResolver $argumentResolver = null
    ) {
        $this->taskStateStore = $taskStateStore ?? new ArrayTaskStateStore();
        $this->logger = new NullLogger();
    }

    public function force(string $id): void
    {
        $this->doRun($this->taskRegistry->get($id), null, true);
    }

    public function run(?\DateTimeInterface $at = null): void
    {
        $at ??= new \DateTimeImmutable();
        foreach ($this->taskRegistry->all() as $task) {
            $this->doRun($task, $at, false);
        }
    }

    /**
     * Do really run task.
     */
    private function doRun(Task $task, ?\DateTimeInterface $at = null, bool $raiseErrors = false): void
    {
        $state = $this->taskStateStore->register($task);

        $lastRun = new \DateTimeImmutable();
        $errorMessage = $errorTrace = null;

        if ($at && !$state->shouldRun($at)) {
            return;
        }

        try {
            $callback = $task->getCallback();
            if ($this->argumentResolver) {
                ($callback)(...$this->argumentResolver->getArguments($callback));
            } else {
                ($callback)();
            }
        } catch (\Throwable $e) {
            $errorMessage = $e->getMessage();
            $errorTrace = $this->normalizeExceptionTrace($e);
            if ($raiseErrors) {
                throw $e;
            }
        } finally {
            $this->taskStateStore->markAsRun($task->getId(), $lastRun, $errorMessage, $errorTrace);
        }
    }

    /**
     * Normalize exception trace.
     */
    private function normalizeExceptionTrace(\Throwable $exception): string
    {
        $output = '';
        do {
            if ($output) {
                $output .= "\n";
            }
            $output .= \sprintf("%s: %s in %s(%s)\n", \get_class($exception), $exception->getMessage(), $exception->getFile(), $exception->getLine());
            $output .= $exception->getTraceAsString();
        } while ($exception = $exception->getPrevious());

        return $output;
    }
}
