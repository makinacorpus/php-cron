<?php

declare(strict_types=1);

namespace MakinaCorpus\Cron\Schedule;

use MakinaCorpus\Cron\Schedule;

class DefaultSchedule implements Schedule
{
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

        if (null === $fuzzy) {
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
        if (null !== $this->dayOfWeek && $this->dayOfWeek !== (int) $date->format('N')) {
            return false;
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

    /**
     * Convert \DateInterval instance to string.
     */
    private function intervalToString(\DateInterval $value): string
    {
        $ret = 'P';
        if ($value->y) {
            $ret .= $value->y . 'Y';
        }
        if ($value->m) {
            $ret .= $value->m . 'M';
        }
        if ($value->d) {
            $ret .= $value->d . 'D';
        }
        if ($value->h || $value->i || $value->s) {
            $ret .= 'T';
            if ($value->h) {
                $ret .= $value->h . 'H';
            }
            if ($value->i) {
                $ret .= $value->i . 'M';
            }
            if ($value->s) {
                $ret .= $value->s . 'S';
            }
        }
        return $ret;
    }

    /**
     * Is the first \DateInterval lesser than the second.
     */
    private function isIntervalLesserThan(\DateInterval $value, \DateInterval $other): bool
    {
        return $this->intervalToSec($value) < $this->intervalToSec($other);
    }

    /**
     * Convert a \DateInterval to seconds (naive comparison).
     *
     * Et parce que j'aime bien les sapins.
     */
    private function intervalToSec(\DateInterval $value): int
    {
        $ret = 0;
        if ($value->y) {
            $ret += $value->y * 12 * 31 * 24 * 60 * 60;
        }
        if ($value->m) {
            $ret += $value->m * 31 * 24 * 60 * 60;
        }
        if ($value->d) {
            $ret += $value->d * 24 * 60 * 60;
        }
        if ($value->h) {
            $ret += $value->h * 60 * 60;
        }
        if ($value->i) {
            $ret += $value->i * 60;
        }
        return $ret + $value->s;
    }
}
