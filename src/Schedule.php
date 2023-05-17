<?php

declare(strict_types=1);

namespace MakinaCorpus\Cron;

/**
 * Cron interval specification.
 *
 * Same as standard UNIX cron, null values mean wildcard.
 */
interface Schedule
{
    const INTERVAL_SEPARATOR = '~';

    /**
     * Is the given date satisfied by.
     */
    public function isStatisfiedBy(\DateTimeInterface $date, ?\DateTimeInterface $previous = null, ?int $fuzzy = null): bool;

    /**
     * Has this instance any data.
     */
    public function isEmpty(): bool;

    /**
     * Get minimum interval between two runs.
     */
    public function getMinInterval(): ?\DateInterval;

    /**
     * String representation of interval string.
     */
    public function getMinIntervalString(): ?string;

    /**
     * Get string representation (without interval).
     *
     * This must give a format that ScheduleFactory::fromString() can parse.
     */
    public function toString(bool $withInterval = false): string;

    /**
     * Alias of toString().
     */
    public function __toString(): string;
}
