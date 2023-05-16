<?php

declare(strict_types=1);

namespace MakinaCorpus\Cron\Tests\Schedule;

use MakinaCorpus\Cron\Schedule\DefaultScheduleFactory;
use PHPUnit\Framework\TestCase;

class DefaultScheduleTest extends TestCase
{
    public function testFromStringWithAliases(): void
    {
        $tested = (new DefaultScheduleFactory())->fromString('@yearly', 'P1DT2M');
        self::assertSame('0 0 1 1 * P1DT2M', $tested->toString(true));
        self::assertSame('0 0 1 1 *', $tested->toString());

        $tested = (new DefaultScheduleFactory())->fromString('@annually');
        self::assertSame('0 0 1 1 *', $tested->toString(true));
        self::assertSame('0 0 1 1 *', $tested->toString());

        $tested = (new DefaultScheduleFactory())->fromString('@monthly');
        self::assertSame('0 0 1 * *', $tested->toString());

        $tested = (new DefaultScheduleFactory())->fromString('@weekly');
        self::assertSame('0 0 * * 7', $tested->toString());

        $tested = (new DefaultScheduleFactory())->fromString('@daily');
        self::assertSame('0 0 * * *', $tested->toString());

        $tested = (new DefaultScheduleFactory())->fromString('@midnight');
        self::assertSame('0 0 * * *', $tested->toString());

        $tested = (new DefaultScheduleFactory())->fromString('@hourly');
        self::assertSame('0 * * * *', $tested->toString());
    }

    public function testIsSpecifiedBy(): void
    {
        $tested = (new DefaultScheduleFactory())->fromString('1 2 3 4 *');

        $date = new \DateTimeImmutable("2023-04-03 02:01:00");
        self::assertTrue($tested->isStatisfiedBy($date));

        // 5 minutes of fuzzy per default.
        $date = new \DateTimeImmutable("2023-04-03 02:05:00");
        self::assertTrue($tested->isStatisfiedBy($date));

        $date = new \DateTimeImmutable("2023-04-03 02:00:00");
        self::assertFalse($tested->isStatisfiedBy($date));

        $date = new \DateTimeImmutable("2023-04-04 02:01:00");
        self::assertFalse($tested->isStatisfiedBy($date));
    }

    public function testIsSpecifiedByDayOfWeek(): void
    {
        $tested = (new DefaultScheduleFactory())->fromString('* * * * 1');

        $date = new \DateTimeImmutable("next monday");
        self::assertTrue($tested->isStatisfiedBy($date));

        $date = new \DateTimeImmutable("next tuesday");
        self::assertFalse($tested->isStatisfiedBy($date));

        $date = new \DateTimeImmutable("next sunday");
        self::assertFalse($tested->isStatisfiedBy($date));
    }

    public function testIsSpecifiedByWithPrevious(): void
    {
        $tested = (new DefaultScheduleFactory())->fromString('* * * 5 *', 'PT10M');
        $previous = new \DateTimeImmutable('2023-05-15 15:45:00');

        $date = new \DateTimeImmutable('2023-05-15 15:50:00');
        self::assertTrue($tested->isStatisfiedBy($date));

        $date = new \DateTimeImmutable('2023-05-15 15:50:00');
        self::assertFalse($tested->isStatisfiedBy($date, $previous));

        $date = new \DateTimeImmutable('2023-05-15 15:58:00');
        self::assertTrue($tested->isStatisfiedBy($date));

        $date = new \DateTimeImmutable('2023-05-15 15:58:00');
        self::assertTrue($tested->isStatisfiedBy($date, $previous));
    }

    public function testAccessors(): void
    {
        $tested = (new DefaultScheduleFactory())->fromString('1 2 3 4 0', 'P1D');

        self::assertNotNull($tested->getMinInterval());
        self::assertSame('1 2 3 4 7', (string) $tested);
    }
}
