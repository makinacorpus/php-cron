<?php

declare(strict_types=1);

namespace MakinaCorpus\Cron;

/**
 * Cron task state.
 */
class TaskState
{
    public function __construct(
        private string $id,
        private bool $active = true,
        private ?\DateTimeInterface $registeredAt = null,
        private ?\DateTimeInterface $lastRun = null,
        private ?Schedule $schedule = null,
        private ?string $errorMessage = null,
        private ?string $errorTrace = null,
    ) {
        if (null === $this->registeredAt) {
            $this->registeredAt = new \DateTimeImmutable();
        }
    }

    /**
     * Get task identifier.
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Is this task active.
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * Was last run an error.
     */
    public function isError(): bool
    {
        return null !== $this->errorMessage;
    }

    /**
     * Get last run error message.
     */
    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    /**
     * Get last run error trace.
     */
    public function getErrorTrace(): ?string
    {
        return $this->errorTrace;
    }

    /**
     * Date this task was registered in store.
     */
    public function getRegistedAt(): \DateTimeInterface
    {
        return $this->registeredAt ?? ($this->registeredAt = new \DateTimeImmutable());
    }

    /**
     * Date this cron ran last.
     */
    public function getLastRun(): ?\DateTimeInterface
    {
        return $this->lastRun;
    }

    /**
     * Get schedule, if null then task will never run.
     */
    public function getSchedule(): ?Schedule
    {
        return $this->schedule;
    }

    /**
     * Is this method returns true, the task is run.
     */
    public function shouldRun(?\DateTimeInterface $reference = null)
    {
        return (bool) $this->schedule?->isStatisfiedBy($reference ?? new \DateTimeImmutable());
    }
}
