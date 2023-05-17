<?php

declare(strict_types=1);

namespace MakinaCorpus\Cron;

/**
 * Cron task.
 *
 * This method is an internal one.
 *
 * Do not implement this method, just register valid callables to the task
 * registry. Using Symfony, this is just as simple as adding the CronTask
 * attribute over a callable class registered as a service, or on any service
 * method.
 */
class Task
{
    private bool $initialized = false;
    private $callback;

    public function __construct(
        callable $callback,
        private ?string $id = null,
        private ?string $name = null,
        private ?string $description = null,
        private ?Schedule $schedule = null,
    ) {
        $this->callback = $callback;
    }

    public function getCallback(): callable
    {
        return $this->callback;
    }

    public function getId(): string
    {
        if (null === $this->id) {
            $this->initialize();
        }
        return $this->id;
    }

    public function getName(): ?string
    {
        if (null === $this->name) {
            $this->initialize();
        }
        return $this->name;
    }

    public function getDescription(): ?string
    {
        if (null === $this->description) {
            $this->initialize();
        }
        return $this->description;
    }

    public function getDefaultSchedule(): ?Schedule
    {
        if (null === $this->schedule) {
            $this->initialize();
        }
        return $this->schedule;
    }

    private function initialize(): void
    {
        if ($this->initialized) {
            return;
        }
        $this->initialized = true;

        $info = CronTask::getForCallback($this->callback);
        if (!$this->id) {
            $this->id = $info->id;
        }
        if (!$this->name) {
            $this->name = $info->name;
        }
        if (!$this->description) {
            $this->description = $info->description;
        }
        if (!$this->schedule) {
            $this->schedule = $info->schedule;
        }
    }
}
