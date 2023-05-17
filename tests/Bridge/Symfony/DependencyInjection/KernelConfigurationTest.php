<?php

declare(strict_types=1);

namespace MakinaCorpus\Cron\Tests\Bridge\Symfony\DependencyInjection;

use MakinaCorpus\Cron\CronRunner;
use MakinaCorpus\Cron\TaskRegistry;
use MakinaCorpus\Cron\Bridge\Symfony\Command\CronDisableAllCommand;
use MakinaCorpus\Cron\Bridge\Symfony\Command\CronRunCommand;
use MakinaCorpus\Cron\Bridge\Symfony\DependencyInjection\CronBundleExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

final class KernelConfigurationTest extends TestCase
{
    private function getContainer(array $parameters = [], array $bundles = [])
    {
        // Code inspired by the SncRedisBundle, all credits to its authors.
        $container = new ContainerBuilder(new ParameterBag($parameters + [
            'kernel.debug'=> false,
            'kernel.bundles' => $bundles,
            'kernel.cache_dir' => \sys_get_temp_dir(),
            'kernel.environment' => 'test',
            'kernel.root_dir' => \dirname(__DIR__),
        ]));

        return $container;
    }

    /**
     * Get minimal config required.
     */
    private function getMinimalConfig(): array
    {
        return [];
    }

    /**
     * Test default config for resulting tagged services
     */
    public function testConfigLoadDefault()
    {
        $config = $this->getMinimalConfig();

        $extension = new CronBundleExtension();
        $extension->load([$config], $container = $this->getContainer());

        self::assertTrue($container->hasDefinition(CronRunner::class));
        self::assertTrue($container->hasDefinition('cron.argument_resolver'));
        self::assertTrue($container->hasAlias(TaskRegistry::class));

        // Console commands.
        self::assertTrue($container->hasDefinition(CronDisableAllCommand::class));
        self::assertTrue($container->hasDefinition(CronRunCommand::class));

        $container->compile();
    }
}
