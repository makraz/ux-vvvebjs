<?php

namespace Makraz\VvvebJsBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('vvvebjs');

        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('cdn_url')
                    ->defaultValue('https://cdn.jsdelivr.net/gh/givanz/VvvebJs@master')
                    ->info('Base URL for VvvebJs assets. Set to a local path to self-host.')
                ->end()
                ->arrayNode('upload')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')
                            ->defaultFalse()
                            ->info('Enable the built-in upload controller.')
                        ->end()
                        ->enumNode('handler')
                            ->values(['local', 'flysystem', 'custom'])
                            ->defaultValue('local')
                            ->info('Upload handler: "local" for filesystem, "flysystem" for League Flysystem, "custom" for your own service.')
                        ->end()
                        ->scalarNode('local_dir')
                            ->defaultValue('%kernel.project_dir%/public/uploads/vvvebjs')
                            ->info('Directory for local uploads.')
                        ->end()
                        ->scalarNode('local_public_path')
                            ->defaultValue('/uploads/vvvebjs')
                            ->info('Public URL path prefix for local uploads.')
                        ->end()
                        ->scalarNode('flysystem_storage')
                            ->defaultNull()
                            ->info('Flysystem storage service ID (e.g. "default.storage").')
                        ->end()
                        ->scalarNode('flysystem_path')
                            ->defaultValue('uploads/vvvebjs')
                            ->info('Path prefix within the Flysystem filesystem.')
                        ->end()
                        ->scalarNode('flysystem_public_url')
                            ->defaultValue('')
                            ->info('Public URL prefix for Flysystem files (e.g. "https://cdn.example.com").')
                        ->end()
                        ->scalarNode('custom_handler')
                            ->defaultNull()
                            ->info('Service ID of your custom UploadHandlerInterface implementation.')
                        ->end()
                        ->integerNode('max_file_size')
                            ->defaultValue(10 * 1024 * 1024)
                            ->info('Maximum upload file size in bytes (default: 10 MB).')
                        ->end()
                        ->arrayNode('allowed_mime_types')
                            ->scalarPrototype()->end()
                            ->defaultValue([
                                'image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml',
                                'video/mp4', 'video/webm',
                                'application/pdf',
                            ])
                            ->info('Allowed MIME types for uploads.')
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
