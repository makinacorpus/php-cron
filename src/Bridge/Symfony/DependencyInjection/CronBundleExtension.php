<?php

declare(strict_types=1);

namespace MakinaCorpus\Cron\Bridge\Symfony\DependencyInjection;

use Goat\Query\Symfony\GoatQueryBundle;
use MakinaCorpus\Cron\TaskStateStore\ArrayTaskStateStore;
use MakinaCorpus\Cron\TaskStateStore\GoatQueryTaskStateStore;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

final class CronBundleExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new YamlFileLoader($container, new FileLocator(\dirname(__DIR__).'/Resources/config'));
        $loader->load('services.yaml');

        if (\class_exists(Command::class)) {
            $loader->load('console.yaml');
        }

        $container->setDefinition('cron.task_state_store', $this->createStateStore($container, $config));
    }

    /**
     * State store registration.
     */
    private function createStateStore(ContainerBuilder $container, array $config): Definition
    {
        $adapter = $config['state']['adapter'] ?? 'auto';
        $options = $config['state']['options'] ?? [];

        if ('auto' === $adapter) {
            if (\in_array(GoatQueryBundle::class, $container->getParameter('kernel.bundles'))) {
                $adapter = 'goat-query';
            } else {
                $adapter = 'memory';
            }
        }

        return match ($adapter) {
            'goat-query' => $this->createStateStoreGoatQuery($container, $config, $options),
            'memory' => (new Definition())->setClass(ArrayTaskStateStore::class)
        };
    }

    /**
     * Create goat-query state store.
     */
    private function createStateStoreGoatQuery(ContainerBuilder $container, array $config, array $options): Definition
    {
        $definition = new Definition();
        $definition->setClass(GoatQueryTaskStateStore::class);
        $definition->setArguments([
            new Reference('goat.runner.' . ($options['goat_query_runner'] ?? 'default')),
            $options['table'] ?? 'public.cron',
        ]);

        return $definition;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfiguration(array $config, ContainerBuilder $container)
    {
        return new CronBundleConfiguration();
    }
}
