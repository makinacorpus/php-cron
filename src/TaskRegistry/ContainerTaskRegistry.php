<?php

declare(strict_types=1);

namespace MakinaCorpus\Cron\TaskRegistry;

use MakinaCorpus\Cron\Task;
use MakinaCorpus\Cron\TaskRegistry;
use MakinaCorpus\Cron\Error\CronConfigurationError;
use MakinaCorpus\Cron\Error\CronTaskDoesNotExistError;
use Psr\Container\ContainerInterface;

class ContainerTaskRegistry implements TaskRegistry
{
    const TYPE_CLASS_INVOKABLE = 1;
    const TYPE_INSTANCE_METHOD = 2;
    const TYPE_STATIC_METHOD = 3;

    public function __construct(private array $services, private ?ContainerInterface $container = null)
    {
    }

    protected function getContainer(): ContainerInterface
    {
        return $this->container ?? throw new CronConfigurationError("Container is not set");
    }

    /**
     * @internal
     * @see \MakinaCorpus\Cron\Bridge\Symfony\DependencyInjection\Compiler\RegisterCronTaskPass
     */
    public static function createReferenceInvokableClass(string $serviceId): array
    {
        return [self::TYPE_CLASS_INVOKABLE, $serviceId];
    }

    /**
     * @internal
     * @see \MakinaCorpus\Cron\Bridge\Symfony\DependencyInjection\Compiler\RegisterCronTaskPass
     */
    public static function createReferenceInstanceMethod(string $serviceId, string $methodName): array
    {
        return [self::TYPE_INSTANCE_METHOD, $serviceId, $methodName];
    }

    /**
     * @internal
     * @see \MakinaCorpus\Cron\Bridge\Symfony\DependencyInjection\Compiler\RegisterCronTaskPass
     */
    public static function createReferenceStaticMethod(string $className, string $methodName): array
    {
        return [self::TYPE_STATIC_METHOD, $className, $methodName];
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $id): Task
    {
        if ($info = ($this->services[$id] ?? null)) {
            $callback = match ($info[0]) {
                self::TYPE_CLASS_INVOKABLE => $this->getContainer()->get($info[1]),
                self::TYPE_INSTANCE_METHOD => [$this->getContainer()->get($info[1]), $info[2]],
                self::TYPE_STATIC_METHOD => [$info[1], $info[2]],
            };
            return new Task($callback);
        }
        throw new CronTaskDoesNotExistError(\sprintf("Cron task '%s' does not exist.", $id));
    }

    /**
     * {@inheritdoc}
     */
    public function has(string $id): bool
    {
        return \array_key_exists($id, $this->services);
    }

    /**
     * {@inheritdoc}
     */
    public function all(): iterable
    {
        foreach (\array_keys($this->services) as $id) {
            yield $this->get($id);
        }
    }
}
