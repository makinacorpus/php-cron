<?php

declare(strict_types=1);

namespace MakinaCorpus\Cron\Schedule;

use MakinaCorpus\Cron\Schedule;

class DefaultSchedule implements Schedule
{
    use ScheduleWithIntervalTrait;

    public function __construct(
        private ?int $minuteOfHour = null,
        private ?int $hourOfDay = null,
        private ?int $dayOfMonth = null,
        private ?int $monthOfYear = null,
        private ?int $dayOfWeek = null,
        private ?\DateInterval $minInterval = null,
    ) {
    }

    /**
     * Is the given date satisfied by.
     */
    public function isStatisfiedBy(\DateTimeInterface $date, ?\DateTimeInterface $previous = null, ?int $fuzzy = null): bool
    {
        if ($this->isEmpty()) {
            return false; // Disallow always run tasks.
        }
        if ($previous && $this->minInterval && $this->isIntervalLesserThan($date->diff($previous), $this->minInterval)) {
            return false; // Called to early.
        }

        if (null === $fuzzy || $fuzzy < 0) {
            $fuzzy = 5; // 5 minutes of delay is allowed.
        }

        if (null !== $this->minuteOfHour) {
            $min = (int) $date->format('i');
            if ($min < $this->minuteOfHour || ($this->minuteOfHour + $fuzzy) < $min) {
                return false;
            }
        }
        if (null !== $this->hourOfDay && $this->hourOfDay !== (int) $date->format('G')) {
            return false;
        }
        if (null !== $this->dayOfMonth && $this->dayOfMonth !== (int) $date->format('j')) {
            return false;
        }
        if (null !== $this->monthOfYear && $this->monthOfYear !== (int) $date->format('n')) {
            return false;
        }
        if (null !== $this->dayOfWeek) {
            $dayOfWeek = $this->dayOfWeek === 0 ? 7 : $this->dayOfWeek;
            if ($dayOfWeek !== (int) $date->format('N')) {
                return false;
            }
        }

        return true;
    }

    public function isEmpty(): bool
    {
        return
            null === $this->minuteOfHour &&
            null === $this->hourOfDay &&
            null === $this->dayOfMonth &&
            null === $this->monthOfYear &&
            null === $this->dayOfWeek
        ;
    }

    public function getMinInterval(): ?\DateInterval
    {
        return $this->minInterval;
    }

    /**
     * Get string representation (without interval).
     */
    public function toString(bool $withInterval = false): string
    {
        $data = [
            $this->minuteOfHour ?? '*',
            $this->hourOfDay ?? '*',
            $this->dayOfMonth ?? '*',
            $this->monthOfYear ?? '*',
            $this->dayOfWeek ?? '*',
        ];

        if ($withInterval && $this->minInterval) {
            $data[] = $this->intervalToString($this->minInterval);
        }

        return \implode(' ', $data);
    }

    /**
     * Alias of toString().
     */
    public function __toString(): string
    {
        return $this->toString();
    }
}
