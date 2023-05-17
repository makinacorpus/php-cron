<?php

declare(strict_types=1);

namespace MakinaCorpus\Cron\Schedule;

use MakinaCorpus\Cron\Schedule;
use MakinaCorpus\Cron\ScheduleFactory;
use MakinaCorpus\Cron\Error\InvalidCronSpecificationError;

class DefaultScheduleFactory implements ScheduleFactory
{
    use ScheduleFactoryTrait;

    /**
     * {@inheritdoc}
     */
    public function fromString(string $spec, ?string $minIntervalSpec = null): Schedule
    {
        list ($spec, $minInterval) = $this->separateSpecAndInterval($spec, $minIntervalSpec);

        $spec = match ($spec) {
            "@yearly" => "0 0 1 1 *",
            "@annually" => "0 0 1 1 *",
            "@monthly" => "0 0 1 * *",
            "@weekly" => "0 0 * * 0",
            "@daily" => "0 0 * * *",
            "@midnight" => "0 0 * * *",
            "@hourly" => "0 * * * *",
            default => $spec,
        };

        $parts = \preg_split('/\s+/', $spec);
        if (\count($parts) !== 5) {
            throw new InvalidCronSpecificationError(\sprintf("Invalid cron specification '%s': must contain 5 parts", $spec));
        }

        $args = [];
        foreach ($parts as $i => $part) {
            if ('*' === $part) {
                $args[] = null;
                continue;
            }

            $name = match ($i) {
                0 => 'minute of hour',
                1 => 'hour of day',
                2 => 'day of month',
                3 => 'month of year',
                4 => 'day of week',
            };

            if (!\ctype_digit($part)) {
                throw new InvalidCronSpecificationError(\sprintf("Invalid cron specification '%s': part %s is not an integer, we only support single unit", $spec, $name));
            }

            $value = (int) $part;

            switch ($i) {
                case 0:
                    if (0 > $value || 59 < $value) {
                        throw new InvalidCronSpecificationError(\sprintf("Invalid cron specification '%s': part %s must be between 0 and 59", $spec, $name));
                    }
                    break;

                case 1:
                    if (0 > $value || 23 < $value) {
                        throw new InvalidCronSpecificationError(\sprintf("Invalid cron specification '%s': part %s must be between 0 and 23", $spec, $name));
                    }
                    break;

                case 2:
                    if (1 > $value || 31 < $value) {
                        throw new InvalidCronSpecificationError(\sprintf("Invalid cron specification '%s': part %s must be between 1 and 31", $spec, $name));
                    }
                    break;

                case 3:
                    if (1 > $value || 12 < $value) {
                        throw new InvalidCronSpecificationError(\sprintf("Invalid cron specification '%s': part %s must be between 1 and 12", $spec, $name));
                    }
                    break;

                case 4:
                    if (0 > $value || 7 < $value) {
                        throw new InvalidCronSpecificationError(\sprintf("Invalid cron specification '%s': part %s must be between 0 and 7", $spec, $name));
                    }
                    break;
            }

            $args[] = $value;
        }

        $args[] = $minInterval;

        return new DefaultSchedule(...$args);
    }
}
