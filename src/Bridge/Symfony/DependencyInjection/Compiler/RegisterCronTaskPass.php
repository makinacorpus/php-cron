<?php

declare(strict_types=1);

namespace MakinaCorpus\Cron\Bridge\Symfony\DependencyInjection\Compiler;

use MakinaCorpus\ArgumentResolver\Bridge\Symfony\DependencyInjection\Compiler\RegisterArgumentResolverPass;
use MakinaCorpus\Cron\CronTask;
use MakinaCorpus\Cron\TaskRegistry\ContainerTaskRegistry;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;

final class RegisterCronTaskPass implements CompilerPassInterface
{
    public function __construct(private string $tagName) {}

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $locatorServices = $callableMap = [];

        foreach ($container->findTaggedServiceIds($this->tagName, true) as $id => $attributes) {
            $definition = $container->getDefinition($id);
            $className = $definition->getClass();

            if (!$refClass = $container->getReflectionClass($className)) {
                throw new InvalidArgumentException(\sprintf('Class "%s" used for service "%s" cannot be found.', $className, $id));
            }

            $methods = [];

            // First handle class as invokable.
            foreach ($refClass->getAttributes(CronTask::class) as $refAttr) {
                \assert($refAttr instanceof \ReflectionAttribute);
                $cronTask = $refAttr->newInstance();
                \assert($cronTask instanceof CronTask);
                $callableMap[$cronTask->id] = ContainerTaskRegistry::createReferenceInvokableClass($id);
                $locatorServices[$id] = new Reference($id);
                $methods[] = '__invoke';
            }

            // Then lookup for methods.
            foreach ($refClass->getMethods() as $refMethod) {
                \assert($refMethod instanceof \ReflectionMethod);
                foreach ($refMethod->getAttributes(CronTask::class) as $refAttr) {
                    \assert($refAttr instanceof \ReflectionAttribute);
                    $cronTask = $refAttr->newInstance();
                    \assert($cronTask instanceof CronTask);
                    $methodName = $refMethod->getName();
                    if ($refMethod->isStatic()) {
                        $callableMap[$cronTask->id] = ContainerTaskRegistry::createReferenceStaticMethod($className, $methodName);
                    } else {
                        $callableMap[$cronTask->id] = ContainerTaskRegistry::createReferenceInstanceMethod($id, $methodName);
                        $locatorServices[$id] = new Reference($id);
                    }
                    $methods[] = $methodName;
                }
            }

            if ($methods) {
                RegisterArgumentResolverPass::registerServiceMethods($container, 'cron', $id, $methods);
            }
        }

        if ($callableMap) {
            $definition = $container->getDefinition('cron.task_registry.container');
            $definition->setArguments([$callableMap, ServiceLocatorTagPass::register($container, $locatorServices)]);
        }
    }
}
