<?php

declare(strict_types=1);

namespace MakinaCorpus\Cron;

/**
 * Responsible of storing execution state of tasks.
 *
 * We are not doing like a POSIX cron, that only works by pattern matching
 * the cron rules and executing everything that matches, but in opposition
 * we are running cron tasks depending upon their previous run date and time
 * to avoid the same task running in parallel twice.
 *
 * Also, by keeping a structure trace of cron run, we are able to store errors
 * along their traces for later debug and monitoring.
 */
interface TaskStateStore
{
    /**
     * Get task state.
     */
    public function get(string $id): TaskState;

    /**
     * Initially register task, or load its state if already exists.
     */
    public function register(Task $task): TaskState;

    /**
     * Delete task.
     */
    public function delete(string $id): void;

    /**
     * Change schedule. If null given, deactivate it along.
     */
    public function schedule(string $id, ?Schedule $schedule): void;

    /**
     * Activate task.
     */
    public function activate(string $id): void;

    /**
     * Deactivate task.
     */
    public function deactivate(string $id): void;

    /**
     * Set last run information.
     */
    public function markAsRun(string $id, \DateTimeInterface $lastRun, ?string $errorMessage, ?string $errorTrace): void;
}
