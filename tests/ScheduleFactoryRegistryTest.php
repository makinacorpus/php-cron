<?php

declare(strict_types=1);

namespace MakinaCorpus\Cron\Tests;

use MakinaCorpus\Cron\ScheduleFactory;
use MakinaCorpus\Cron\ScheduleFactoryRegistry;
use MakinaCorpus\Cron\Error\CronConfigurationError;
use MakinaCorpus\Cron\Schedule\DefaultScheduleFactory;
use PHPUnit\Framework\TestCase;

class ScheduleFactoryRegistryTest extends TestCase
{
    protected function setUp(): void
    {
        ScheduleFactoryRegistry::reset();
    }

    public function testSet(): void
    {
        $factory = new DefaultScheduleFactory();

        ScheduleFactoryRegistry::set($factory);
        self::assertSame($factory, ScheduleFactoryRegistry::get());
    }

    public function testSetRaiseErrorIfSet(): void
    {
        $factory = new DefaultScheduleFactory();

        ScheduleFactoryRegistry::get();

        self::expectException(CronConfigurationError::class);
        ScheduleFactoryRegistry::set($factory);
    }

    public function testGetLazyInstanciateIfEmpty(): void
    {
        $factory = ScheduleFactoryRegistry::get();
        self::assertInstanceOf(ScheduleFactory::class, $factory);
    }
}