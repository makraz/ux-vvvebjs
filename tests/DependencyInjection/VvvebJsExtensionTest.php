<?php

namespace Makraz\VvvebJsBundle\Tests\DependencyInjection;

use Makraz\VvvebJsBundle\Controller\VvvebJsUploadController;
use Makraz\VvvebJsBundle\DependencyInjection\VvvebJsExtension;
use Makraz\VvvebJsBundle\Upload\FlysystemUploadHandler;
use Makraz\VvvebJsBundle\Upload\LocalUploadHandler;
use Makraz\VvvebJsBundle\Upload\UploadHandlerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class VvvebJsExtensionTest extends TestCase
{
    public function testLoadWithUploadDisabled(): void
    {
        $container = $this->createContainer();
        $extension = new VvvebJsExtension();

        $extension->load([['upload' => ['enabled' => false]]], $container);

        $this->assertFalse($container->hasDefinition(UploadHandlerInterface::class));
        $this->assertFalse($container->hasDefinition(VvvebJsUploadController::class));
    }

    public function testLoadSetsCdnUrlParameter(): void
    {
        $container = $this->createContainer();
        $extension = new VvvebJsExtension();

        $extension->load([['cdn_url' => 'https://my-cdn.com/vvvebjs']], $container);

        $this->assertSame('https://my-cdn.com/vvvebjs', $container->getParameter('vvvebjs.cdn_url'));
    }

    public function testLoadWithLocalHandler(): void
    {
        $container = $this->createContainer();
        $extension = new VvvebJsExtension();

        $extension->load([['upload' => [
            'enabled' => true,
            'handler' => 'local',
            'local_dir' => '/tmp/uploads',
            'local_public_path' => '/uploads',
        ]]], $container);

        $this->assertTrue($container->hasDefinition(UploadHandlerInterface::class));
        $this->assertTrue($container->hasDefinition(VvvebJsUploadController::class));

        $handlerDef = $container->getDefinition(UploadHandlerInterface::class);
        $this->assertSame(LocalUploadHandler::class, $handlerDef->getClass());
    }

    public function testLoadWithCustomHandler(): void
    {
        $container = $this->createContainer();
        $extension = new VvvebJsExtension();

        $extension->load([['upload' => [
            'enabled' => true,
            'handler' => 'custom',
            'custom_handler' => 'app.my_handler',
        ]]], $container);

        $this->assertTrue($container->hasAlias(UploadHandlerInterface::class));
    }

    public function testCustomHandlerWithoutServiceIdThrows(): void
    {
        $container = $this->createContainer();
        $extension = new VvvebJsExtension();

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('custom_handler');

        $extension->load([['upload' => [
            'enabled' => true,
            'handler' => 'custom',
        ]]], $container);
    }

    public function testFlysystemHandlerWithoutStorageThrows(): void
    {
        $container = $this->createContainer();
        $extension = new VvvebJsExtension();

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('flysystem_storage');

        $extension->load([['upload' => [
            'enabled' => true,
            'handler' => 'flysystem',
        ]]], $container);
    }

    public function testFlysystemHandlerRegistersCorrectly(): void
    {
        $container = $this->createContainer();
        $extension = new VvvebJsExtension();

        $extension->load([['upload' => [
            'enabled' => true,
            'handler' => 'flysystem',
            'flysystem_storage' => 'default.storage',
            'flysystem_path' => 'media/vvvebjs',
            'flysystem_public_url' => 'https://cdn.example.com',
        ]]], $container);

        $this->assertTrue($container->hasDefinition(UploadHandlerInterface::class));
        $handlerDef = $container->getDefinition(UploadHandlerInterface::class);

        $this->assertSame(FlysystemUploadHandler::class, $handlerDef->getClass());
        $this->assertSame('media/vvvebjs', $handlerDef->getArgument('$uploadPath'));
        $this->assertSame('https://cdn.example.com', $handlerDef->getArgument('$publicUrlPrefix'));
    }

    public function testUploadControllerReceivesMaxFileSizeAndMimeTypes(): void
    {
        $container = $this->createContainer();
        $extension = new VvvebJsExtension();

        $extension->load([['upload' => [
            'enabled' => true,
            'handler' => 'local',
            'max_file_size' => 10_000_000,
            'allowed_mime_types' => ['image/png'],
        ]]], $container);

        $controllerDef = $container->getDefinition(VvvebJsUploadController::class);
        $this->assertSame(10_000_000, $controllerDef->getArgument('$maxFileSize'));
        $this->assertSame(['image/png'], $controllerDef->getArgument('$allowedMimeTypes'));
    }

    public function testPrependAddsTwigFormTheme(): void
    {
        $container = $this->createContainer();
        $container->setParameter('kernel.bundles', ['TwigBundle' => 'Symfony\Bundle\TwigBundle\TwigBundle']);

        $extension = new VvvebJsExtension();
        $extension->prepend($container);

        $twigConfig = $container->getExtensionConfig('twig');
        $this->assertNotEmpty($twigConfig);

        $formThemes = $twigConfig[0]['form_themes'] ?? [];
        $this->assertContains('@VvvebJs/form.html.twig', $formThemes);
    }

    public function testPrependSkipsTwigWhenNotInstalled(): void
    {
        $container = $this->createContainer();
        $container->setParameter('kernel.bundles', []);

        $extension = new VvvebJsExtension();
        $extension->prepend($container);

        $twigConfig = $container->getExtensionConfig('twig');
        $this->assertEmpty($twigConfig);
    }

    public function testControllerIsPublic(): void
    {
        $container = $this->createContainer();
        $extension = new VvvebJsExtension();

        $extension->load([['upload' => [
            'enabled' => true,
            'handler' => 'local',
        ]]], $container);

        $controllerDef = $container->getDefinition(VvvebJsUploadController::class);
        $this->assertTrue($controllerDef->isPublic());
    }

    public function testControllerHasServiceArgumentsTag(): void
    {
        $container = $this->createContainer();
        $extension = new VvvebJsExtension();

        $extension->load([['upload' => [
            'enabled' => true,
            'handler' => 'local',
        ]]], $container);

        $controllerDef = $container->getDefinition(VvvebJsUploadController::class);
        $this->assertTrue($controllerDef->hasTag('controller.service_arguments'));
    }

    public function testPrependAddsAssetMapperPathWhenAvailable(): void
    {
        $frameworkBundlePath = (new \ReflectionClass(\Symfony\Bundle\FrameworkBundle\FrameworkBundle::class))->getFileName();
        $frameworkBundleDir = \dirname($frameworkBundlePath);

        $container = $this->createContainer();
        $container->setParameter('kernel.bundles', []);
        $container->setParameter('kernel.bundles_metadata', [
            'FrameworkBundle' => ['path' => $frameworkBundleDir],
        ]);

        $extension = new VvvebJsExtension();
        $extension->prepend($container);

        $frameworkConfig = $container->getExtensionConfig('framework');
        $this->assertNotEmpty($frameworkConfig);

        $paths = $frameworkConfig[0]['asset_mapper']['paths'] ?? [];
        $this->assertNotEmpty($paths);
        $this->assertContains('@makraz/ux-vvvebjs', $paths);
    }

    public function testPrependSkipsAssetMapperWhenFrameworkBundleMissing(): void
    {
        $container = $this->createContainer();
        $container->setParameter('kernel.bundles', []);
        $container->setParameter('kernel.bundles_metadata', []);

        $extension = new VvvebJsExtension();
        $extension->prepend($container);

        $frameworkConfig = $container->getExtensionConfig('framework');
        $this->assertEmpty($frameworkConfig);
    }

    public function testPrependSkipsAssetMapperWhenBundlesMetadataNotArray(): void
    {
        $container = $this->createContainer();
        $container->setParameter('kernel.bundles', []);
        $container->setParameter('kernel.bundles_metadata', 'invalid');

        $extension = new VvvebJsExtension();
        $extension->prepend($container);

        $frameworkConfig = $container->getExtensionConfig('framework');
        $this->assertEmpty($frameworkConfig);
    }

    public function testDefaultConfigRegistersFormTypeService(): void
    {
        $container = $this->createContainer();
        $extension = new VvvebJsExtension();

        $extension->load([[]], $container);

        $this->assertTrue($container->hasDefinition('form.ux_vvvebjs'));
    }

    public function testLocalHandlerArguments(): void
    {
        $container = $this->createContainer();
        $extension = new VvvebJsExtension();

        $extension->load([['upload' => [
            'enabled' => true,
            'handler' => 'local',
            'local_dir' => '/var/www/uploads',
            'local_public_path' => '/media/uploads',
        ]]], $container);

        $handlerDef = $container->getDefinition(UploadHandlerInterface::class);
        $this->assertSame('/var/www/uploads', $handlerDef->getArgument('$uploadDir'));
        $this->assertSame('/media/uploads', $handlerDef->getArgument('$publicPath'));
    }

    private function createContainer(): ContainerBuilder
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.project_dir', sys_get_temp_dir());
        $container->setParameter('kernel.bundles', []);
        $container->setParameter('kernel.bundles_metadata', []);

        return $container;
    }
}
