<?php

declare(strict_types=1);

namespace MakinaCorpus\Cron\Bridge\Symfony;

use MakinaCorpus\Cron\CronTask;
use MakinaCorpus\Cron\Bridge\Symfony\DependencyInjection\CronBundleExtension;
use MakinaCorpus\Cron\Bridge\Symfony\DependencyInjection\Compiler\RegisterCronTaskPass;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class CronBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container->registerAttributeForAutoconfiguration(
            CronTask::class,
            static function (
                ChildDefinition $definition,
                CronTask $attribute,
                \ReflectionClass|\ReflectionMethod $reflector
            ): void {
                $definition->addTag('cron.task_holder');
            }
        );

        $container->addCompilerPass(new RegisterCronTaskPass('cron.task_holder'));
    }

    /**
     * {@inheritdoc}
     */
    public function getContainerExtension(): ?ExtensionInterface
    {
        return new CronBundleExtension();
    }
}
