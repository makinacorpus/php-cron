<?php

declare(strict_types=1);

namespace MakinaCorpus\Cron;

use MakinaCorpus\Cron\Error\CronConfigurationError;
use MakinaCorpus\Cron\Schedule\DefaultScheduleFactory;
use PHPUnit\Framework\TestCase;

/**
 * Schedule factory is a singleton that needs to be initialized before first
 * usage, for consistency.
 */
final class ScheduleFactoryRegistry
{
    private static ?ScheduleFactory $factory = null;

    /**
     * @codeCoverageIgnore
     */
    private function __construct()
    {
    }

    public static function get(): ScheduleFactory
    {
        if (null === self::$factory) {
            self::$factory = new DefaultScheduleFactory();
        }
        return self::$factory;
    }

    public static function set(ScheduleFactory $factory): void
    {
        if (null !== self::$factory) {
            throw new CronConfigurationError(\sprintf("%s is already initialized.", __CLASS__));
        }
        self::$factory = $factory;
    }

    /**
     * @codeCoverageIgnore
     * @internal
     *   For unit tests only.
     */
    public static function reset(): void
    {
        if (!\class_exists(TestCase::class)) {
            throw new CronConfigurationError("This method if for unit testing only.");
        }
        self::$factory = null;
    }
}
