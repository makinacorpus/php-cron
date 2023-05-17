<?php

declare(strict_types=1);

namespace MakinaCorpus\Cron\TaskStateStore;

use MakinaCorpus\Cron\Schedule;
use MakinaCorpus\Cron\Task;
use MakinaCorpus\Cron\TaskState;
use MakinaCorpus\Cron\TaskStateStore;
use MakinaCorpus\Cron\Error\CronTaskDoesNotExistError;

/**
 * Used when state store is not configured.
 */
class ArrayTaskStateStore implements TaskStateStore
{
    private array $active = [];
    private array $schedule = [];
    private array $store = [];

    /**
     * {@inheritdoc}
     */
    public function get(string $id): TaskState
    {
        return $this->store[$id] ?? throw CronTaskDoesNotExistError::create($id);
    }

    /**
     * {@inheritdoc}
     */
    public function register(Task $task): TaskState
    {
        $id = $task->getId();

        return $this->store[$id] = new TaskState(id: $id, schedule: $task->getDefaultSchedule(), active: $this->active[$id] ?? true);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(string $id): void
    {
        unset(
            $this->active[$id],
            $this->schedule[$id],
            $this->store[$id],
        );
    }

    /**
     * {@inheritdoc}
     */
    public function schedule(string $id, ?Schedule $schedule): void
    {
        $state = $this->get($id);

        (\Closure::bind(
            fn () => $state->schedule = $schedule,
            null,
            TaskState::class
        ))();
    }

    /**
     * {@inheritdoc}
     */
    public function activate(string $id): void
    {
        $state = $this->get($id);

        (\Closure::bind(
            fn () => $state->active = true,
            null,
            TaskState::class
        ))();
    }

    /**
     * {@inheritdoc}
     */
    public function deactivate(string $id): void
    {
        $state = $this->get($id);

        (\Closure::bind(
            fn () => $state->active = false,
            null,
            TaskState::class
        ))();
    }

    /**
     * {@inheritdoc}
     */
    public function markAsRun(string $id, \DateTimeInterface $lastRun, ?string $errorMessage, ?string $errorTrace): void
    {
        $state = $this->get($id);

        (\Closure::bind(
            function () use ($state, $lastRun, $errorMessage, $errorTrace) {
                $state->lastRun = $lastRun;
                $state->errorMessage = $errorMessage;
                $state->errorTrace = $errorTrace;
            },
            null,
            TaskState::class
        ))();
    }
}
