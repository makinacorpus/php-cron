<?php

declare(strict_types=1);

namespace MakinaCorpus\Cron\Tests;

use MakinaCorpus\ArgumentResolver\DefaultArgumentResolver;
use MakinaCorpus\ArgumentResolver\Context\ResolverContext;
use MakinaCorpus\ArgumentResolver\Metadata\ArgumentMetadata;
use MakinaCorpus\ArgumentResolver\Resolver\ArgumentValueResolver;
use MakinaCorpus\Cron\CronRunner;
use MakinaCorpus\Cron\CronTask;
use MakinaCorpus\Cron\TaskRegistry\ArrayTaskRegistry;
use MakinaCorpus\Cron\TaskStateStore\ArrayTaskStateStore;
use PHPUnit\Framework\TestCase;

class CronRunnerTest extends TestCase
{
    public function testForce(): void
    {
        $canary = false;

        $taskRegistry = new ArrayTaskRegistry([
            #[CronTask(id: 'foo', schedule: '* * 1 * *')]
            function () use (&$canary) {
                $canary = true;
            }
        ]);

        $tested = new CronRunner($taskRegistry, new ArrayTaskStateStore());

        $tested->run(new \DateTimeImmutable('2023-05-16 00:00:00'));
        self::assertFalse($canary);

        $tested->force('foo');
        self::assertTrue($canary);
    }

    public function testForceRaiseErrors(): void
    {
        $taskRegistry = new ArrayTaskRegistry([
            #[CronTask(id: 'foo', schedule: '* * 1 * *')]
            fn () => throw new \DomainException("This is an exception")
        ]);

        $tested = new CronRunner($taskRegistry, new ArrayTaskStateStore());

        self::expectExceptionMessage("This is an exception");
        $tested->force('foo');
    }

    public function testArgumentResolver(): void
    {
        $canary = null;

        $taskRegistry = new ArrayTaskRegistry([
            #[CronTask(id: 'foo', schedule: '* * 1 * *')]
            function (int $foo) use (&$canary) {
                $canary = $foo;
            }
        ]);

        $argumentResolver = new DefaultArgumentResolver(null, [
            new class () implements ArgumentValueResolver
            {
                public function supports(ArgumentMetadata $argument, ResolverContext $context): bool
                {
                    return true;
                }

                public function resolve(ArgumentMetadata $argument, ResolverContext $context): iterable
                {
                    yield 12;
                }
            }
        ]);

        $tested = new CronRunner($taskRegistry, new ArrayTaskStateStore(), $argumentResolver);
        $tested->force('foo');
        self::assertSame(12, $canary);
    }

    public function testRun(): void
    {
        $canary = false;

        $taskRegistry = new ArrayTaskRegistry([
            #[CronTask(id: 'foo', schedule: '* * 1 * *')]
            function () use (&$canary) {
                $canary = true;
            }
        ]);

        $tested = new CronRunner($taskRegistry, new ArrayTaskStateStore());
        self::assertFalse($canary);

        $tested->run(new \DateTimeImmutable('2023-05-16 00:00:00'));
        self::assertFalse($canary);

        $tested->run(new \DateTimeImmutable('2023-05-01 00:00:00'));
        self::assertTrue($canary);
    }

    public function testRunScenario(): void
    {
        $canary1 = false;
        $canary2 = false;

        $taskRegistry = new ArrayTaskRegistry([
            #[CronTask(id: 'foo', schedule: '* * 1 * *')]
            function () use (&$canary1) {
                $canary1 = true;
            },
            #[CronTask(id: 'bar', schedule: '* * 2 * *')]
            function () use (&$canary2) {
                $canary2 = true;
            },
        ]);

        $tested = new CronRunner($taskRegistry, new ArrayTaskStateStore());
        self::assertFalse($canary1);
        self::assertFalse($canary2);

        $tested->run(new \DateTimeImmutable('2023-05-01 00:00:00'));
        self::assertTrue($canary1);
        self::assertFalse($canary2);

        $tested->run(new \DateTimeImmutable('2023-05-02 00:00:00'));
        self::assertTrue($canary1);
        self::assertTrue($canary2);
    }

    public function testSurviveInCaseOfError(): void
    {
        $canary = false;

        $taskRegistry = new ArrayTaskRegistry([
            #[CronTask(id: 'bar', schedule: '* * 1 * *')]
            function () {
                throw new \Exception("Bouh.");
            },
            #[CronTask(id: 'foo', schedule: '* * 1 * *')]
            function () use (&$canary) {
                $canary = true;
            },
        ]);

        $taskStateStore = new ArrayTaskStateStore();

        $tested = new CronRunner($taskRegistry, $taskStateStore);
        self::assertFalse($canary);

        $tested->run(new \DateTimeImmutable('2023-05-01 00:00:00'));
        self::assertTrue($canary);
        self::assertSame("Bouh.", $taskStateStore->get('bar')->getErrorMessage());
        self::assertNotEmpty($taskStateStore->get('bar')->getErrorTrace());
    }
}
