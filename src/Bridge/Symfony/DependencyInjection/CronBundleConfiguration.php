<?php

declare(strict_types=1);

namespace MakinaCorpus\Cron\Bridge\Symfony\DependencyInjection;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

final class CronBundleConfiguration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('cron');

        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->arrayNode('state')
                    ->children()
                        ->enumNode('adapter')
                            ->values(['goat-query', 'memory', 'auto'])
                            ->defaultValue('auto')
                        ->end()
                        ->arrayNode('options')
                            ->children()
                                ->scalarNode('goat_query_runner')->defaultValue('default')->end()
                                ->scalarNode('table_name')->defaultValue('public.cron')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
