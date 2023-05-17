<?php

declare(strict_types=1);

namespace MakinaCorpus\Cron\Schedule;

use Cron\CronExpression;
use MakinaCorpus\Cron\Schedule;

class CronExpressionSchedule implements Schedule
{
    use ScheduleWithIntervalTrait;

    public function __construct(
        private CronExpression $cronExpression,
        private ?\DateInterval $minInterval = null,
    ) {}

    /**
     * {@inheritdoc}
     */
    public function isStatisfiedBy(\DateTimeInterface $date, ?\DateTimeInterface $previous = null, ?int $fuzzy = null): bool
    {
        if ($previous && $this->minInterval && $this->isIntervalLesserThan($date->diff($previous), $this->minInterval)) {
            return false; // Called to early.
        }

        if (null === $fuzzy || $fuzzy < 0) {
            $fuzzy = 5; // 5 minutes of delay is allowed.
        }

        if ($this->cronExpression->isDue($date)) {
            return true;
        }

        if ($fuzzy && 0 < $fuzzy) {
            $date = \DateTimeImmutable::createFromInterface($date);

            for ($i = 1; $i <= $fuzzy; ++$i) {
                $reference = $date->sub(new \DateInterval(\sprintf("PT%dM", $i)));

                if ($this->cronExpression->isDue($reference)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function isEmpty(): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getMinInterval(): ?\DateInterval
    {
        return $this->minInterval;
    }

    /**
     * {@inheritdoc}
     */
    public function getMinIntervalString(): ?string
    {
        return $this->minInterval ? $this->intervalToString($this->minInterval) : null;
    }

    /**
     * {@inheritdoc}
     */
    public function toString(bool $withInterval = false): string
    {
        $ret = (string) $this->cronExpression->getExpression();

        if ($withInterval && $this->minInterval) {
            return \sprintf('%s %s %s', $ret, Schedule::INTERVAL_SEPARATOR, $this->intervalToString($this->minInterval));
        }
        return $ret;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString(): string
    {
        return $this->toString();
    }
}
