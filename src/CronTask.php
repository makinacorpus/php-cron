<?php

declare(strict_types=1);

namespace MakinaCorpus\Cron;

/**
 * Use this attribute on an invokable service class or a service method to
 * define a cron task.
 */
#[\Attribute(flags: \Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD | \Attribute::TARGET_FUNCTION)]
class CronTask
{
    public ?Schedule $schedule;

    public function __construct(
        public string $id,
        public ?string $name = null,
        public ?string $description = null,
        null|string|Schedule $schedule = null
    ) {
        if (null === $schedule) {
            $this->schedule = null;
        } else if (\is_string($schedule)) {
            $this->schedule = ScheduleFactoryRegistry::get()->fromString($schedule);
        } else if ($schedule instanceof Schedule) {
            $this->schedule = $schedule;
        }
    }

    /**
     * Find cron information for the given callable.
     */
    public static function getForCallback(callable $callback): self
    {
        if (!$callback instanceof \Closure) {
            if (\is_object($callback) && \method_exists($callback, '__invoke')) {
                return self::getForObject($callback);
            }
            $callback = \Closure::fromCallable($callback);
        }
        $refFunc = new \ReflectionFunction($callback);
        foreach ($refFunc->getAttributes(CronTask::class) as $refAttr) {
            \assert($refAttr instanceof \ReflectionAttribute);
            return $refAttr->newInstance();
        }
        if ($refClass = $refFunc->getClosureScopeClass()) {
            return new self($refClass->getName() . '::' . $refFunc->getName());
        }
        return new self($refFunc->getName());
    }

    private static function getForObject(object $object): self
    {
        $refClass = new \ReflectionClass($object);
        foreach ($refClass->getAttributes(CronTask::class) as $refAttr) {
            \assert($refAttr instanceof \ReflectionAttribute);
            return $refAttr->newInstance();
        }
        return new self($refClass->getName());
    }
}
