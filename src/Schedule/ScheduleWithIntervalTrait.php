<?php

declare(strict_types=1);

namespace MakinaCorpus\Cron\Schedule;

trait ScheduleWithIntervalTrait
{
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
